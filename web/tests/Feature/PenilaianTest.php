<?php

use App\Models\Agent;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makePenilaianReservation(array $overrides = []): Reservation
{
    return Reservation::create(array_merge([
        'nama_lengkap' => 'Rafi Rizqallah Andila',
        'jabatan' => 'Staf Pengadaan',
        'asal_pt' => 'PT Millenio Amerta Data',
        'jenis_layanan' => 'Konsultasi SPSE / Pengadaan Elektronik',
        'detail_keperluan' => 'Diskusi terkait pengadaan barang',
        'tanggal_jam' => '2026-04-13 08:00:00',
        'kode_reservasi' => 'RES-20260413-AAAA',
        'status' => 'completed',
        'waktu_selesai_tatap_muka' => '2026-04-13 08:40:00',
    ], $overrides));
}

/**
 * Payload penilaian Likert yang selalu lengkap untuk kelima pertanyaan.
 */
function validRatingPayload(array $overrides = []): array
{
    return array_merge([
        'rating_kemudahan' => 5,
        'rating_efisiensi' => 4,
        'rating_sikap' => 5,
        'rating_kejelasan' => 4,
        'rating_kepuasan' => 5,
    ], $overrides);
}

test('rating page can be opened without login', function () {
    $response = $this->get(route('penilaian'));

    $response->assertOk();
    $response->assertSee('Kode Reservasi');
    $response->assertSee('Ketentuan Penilaian');
});

test('valid reservation code redirects to its rating page', function () {
    $reservation = makePenilaianReservation();

    $response = $this->get(route('penilaian', [
        'kode_reservasi' => $reservation->kode_reservasi,
    ]));

    $response->assertRedirect(route('penilaian.show', [
        'kodeReservasi' => $reservation->kode_reservasi,
    ]));
});

test('unknown reservation code redirects back with an error', function () {
    $response = $this->get(route('penilaian', [
        'kode_reservasi' => 'RES-20260413-ZZZZ',
    ]));

    $response->assertRedirect(route('penilaian'));
    $response->assertSessionHasErrors('kode_reservasi');
});

test('rating page shows guest and agent details', function () {
    $agent = Agent::create([
        'name' => 'Agent 7',
        'email' => 'agent.rating.test@temulkpp.com',
        'password' => 'password',
        'is_active' => true,
    ]);

    $reservation = makePenilaianReservation([
        'agent_id' => $agent->id,
        'asal_pt' => 'PT Bangun Negeri Sentosa',
    ]);

    $response = $this->get(route('penilaian.show', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee($reservation->kode_reservasi);
    $response->assertSee($reservation->nama_lengkap);
    $response->assertSee($reservation->jabatan);
    $response->assertSee('PT Bangun Negeri Sentosa');
    $response->assertSee('Agent 7');
    $response->assertSee($reservation->jenis_layanan);
});

test('rating form shows all five likert questions', function () {
    Carbon::setTestNow('2026-04-13 15:30:00');

    $reservation = makePenilaianReservation();

    $response = $this->get(route('penilaian.show', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee('Kirim Penilaian');

    foreach (Reservation::RATING_QUESTIONS as $column => $question) {
        $response->assertSee($question);
        $response->assertSee('name="' . $column . '"', false);
    }

    foreach (Reservation::RATING_STATEMENTS as $statement) {
        $response->assertSee($statement);
    }

    Carbon::setTestNow();
});

test('rating form is hidden when the service is not completed yet', function () {
    Carbon::setTestNow('2026-04-13 09:00:00');

    $reservation = makePenilaianReservation([
        'status' => 'pending',
        'waktu_selesai_tatap_muka' => null,
    ]);

    $response = $this->get(route('penilaian.show', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee('hanya tersedia setelah sesi tatap muka');
    $response->assertDontSee('Kirim Penilaian');

    Carbon::setTestNow();
});

test('rating form is shown when the service is completed on the same day', function () {
    Carbon::setTestNow('2026-04-13 15:30:00');

    $reservation = makePenilaianReservation();

    $response = $this->get(route('penilaian.show', $reservation->kode_reservasi));

    $response->assertOk();
    $response->assertSee('Beri Penilaian');
    $response->assertSee('Kirim Penilaian');

    Carbon::setTestNow();
});

test('all five ratings are stored on the reservation', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation();

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), [
        ...validRatingPayload(),
        'rating_comment' => 'Pelayanan sangat membantu.',
    ]);

    $response->assertRedirect(route('penilaian.show', $reservation->kode_reservasi));
    $response->assertSessionHas('rating_success');

    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'rating_kemudahan' => 5,
        'rating_efisiensi' => 4,
        'rating_sikap' => 5,
        'rating_kejelasan' => 4,
        'rating_kepuasan' => 5,
        'rating_comment' => 'Pelayanan sangat membantu.',
    ]);

    $refreshed = $reservation->refresh();

    expect($refreshed->rated_at)->not->toBeNull();
    expect($refreshed->has_complete_rating)->toBeTrue();
    expect($refreshed->average_rating)->toEqualWithDelta(4.6, 0.01);

    Carbon::setTestNow();
});

test('each likert question is required', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation();

    $response = $this->from(route('penilaian.show', $reservation->kode_reservasi))
        ->post(route('penilaian.store', $reservation->kode_reservasi), [
            'rating_comment' => 'Tanpa nilai.',
        ]);

    $response->assertSessionHasErrors(array_keys(Reservation::RATING_QUESTIONS));

    expect($reservation->refresh()->has_complete_rating)->toBeFalse();
    expect($reservation->refresh()->rated_at)->toBeNull();

    Carbon::setTestNow();
});

test('likert answers must be between one and five', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation();

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), [
        ...validRatingPayload(['rating_sikap' => 9]),
    ]);

    $response->assertSessionHasErrors('rating_sikap');

    expect($reservation->refresh()->has_complete_rating)->toBeFalse();
    expect($reservation->refresh()->rating_sikap)->toBeNull();

    Carbon::setTestNow();
});

test('rating comment is optional', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation();

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), validRatingPayload());

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'rating_kemudahan' => 5,
        'rating_comment' => null,
    ]);

    Carbon::setTestNow();
});

test('rating cannot be submitted on a different day than the service finished', function () {
    Carbon::setTestNow('2026-04-14 09:00:00');

    $reservation = makePenilaianReservation([
        'waktu_selesai_tatap_muka' => '2026-04-13 08:40:00',
    ]);

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), validRatingPayload());

    $response->assertRedirect(route('penilaian.show', $reservation->kode_reservasi));
    $response->assertSessionHasErrors('rating');

    expect($reservation->refresh()->has_complete_rating)->toBeFalse();
    expect($reservation->refresh()->rated_at)->toBeNull();

    Carbon::setTestNow();
});

test('already rated reservation cannot be rated again', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation([
        ...validRatingPayload([
            'rating_kemudahan' => 3,
            'rating_efisiensi' => 3,
            'rating_sikap' => 3,
            'rating_kejelasan' => 3,
            'rating_kepuasan' => 3,
        ]),
        'rating_comment' => 'Penilaian pertama.',
        'rated_at' => '2026-04-13 12:00:00',
    ]);

    $page = $this->get(route('penilaian.show', $reservation->kode_reservasi));

    $page->assertOk();
    $page->assertSee('sudah Anda berikan sebelumnya');
    $page->assertDontSee('Kirim Penilaian');

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), [
        ...validRatingPayload([
            'rating_kemudahan' => 1,
            'rating_efisiensi' => 1,
            'rating_sikap' => 1,
            'rating_kejelasan' => 1,
            'rating_kepuasan' => 1,
        ]),
        'rating_comment' => 'Penilaian kedua.',
    ]);

    $response->assertSessionHasErrors('rating');

    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'rating_kemudahan' => 3,
        'rating_kepuasan' => 3,
        'rating_comment' => 'Penilaian pertama.',
    ]);

    Carbon::setTestNow();
});

test('rating a service without a recorded finish time is not allowed', function () {
    Carbon::setTestNow('2026-04-13 09:00:00');

    $reservation = makePenilaianReservation([
        'status' => 'completed',
        'waktu_selesai_tatap_muka' => null,
        'tanggal_jam' => '2026-04-10 08:00:00',
    ]);

    $response = $this->post(route('penilaian.store', $reservation->kode_reservasi), validRatingPayload());

    $response->assertSessionHasErrors('rating');

    expect($reservation->refresh()->has_complete_rating)->toBeFalse();

    Carbon::setTestNow();
});

test('submitting a rating does not change the reservation status', function () {
    Carbon::setTestNow('2026-04-13 16:00:00');

    $reservation = makePenilaianReservation();

    $this->post(route('penilaian.store', $reservation->kode_reservasi), validRatingPayload());

    $this->assertDatabaseHas('reservations', [
        'kode_reservasi' => $reservation->kode_reservasi,
        'status' => 'completed',
    ]);

    Carbon::setTestNow();
});
