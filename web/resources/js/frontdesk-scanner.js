import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
import jsQR from 'jsqr';
import QrScanner from 'qr-scanner';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const RESERVATION_CODE_PATTERN = /^RES-\d{8}-[A-Z0-9]{4}$/;
const REAR_CAMERA_LABEL_PATTERN = /(back|rear|environment|belakang)/i;
const IGNORED_CAMERA_LABEL_PATTERN = /(virtual|obs|infrared|\bir\b|windows hello|snap camera|droidcam|iriun)/i;

function cameraErrorMessage(error) {
    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
        return 'Akses kamera membutuhkan HTTPS atau alamat localhost.';
    }

    const errorName = error?.name ?? '';
    const errorDetail = error?.message ?? String(error ?? 'Unknown camera error');

    if (['NotAllowedError', 'PermissionDeniedError'].includes(errorName)) {
        return 'Izin kamera ditolak. Izinkan kamera untuk localhost melalui pengaturan situs browser.';
    }

    if (['NotFoundError', 'DevicesNotFoundError'].includes(errorName)) {
        return 'Kamera tidak ditemukan. Pastikan webcam terhubung dan aktif.';
    }

    if (['NotReadableError', 'TrackStartError'].includes(errorName)) {
        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut lalu coba lagi.';
    }

    if (['OverconstrainedError', 'ConstraintNotSatisfiedError'].includes(errorName)) {
        return 'Kamera tidak mendukung pengaturan yang diminta. Pilih kamera lain atau mulai ulang scanner.';
    }

    return `Kamera gagal dibuka. Periksa izin dan koneksi webcam. (${errorDetail})`;
}

export function initializeFrontDeskScanner() {
    const page = document.getElementById('frontdesk-page');

    if (!page) {
        return;
    }

    const frontDeskUrl = page.dataset.frontdeskUrl || '/front-desk';
    const successMessage = page.dataset.checkInSuccess || '';
    const errorMessage = page.dataset.checkInError || '';
    const codeInput = document.getElementById('kode_reservasi');
    const manualConfirmForm = document.getElementById('manual-confirm-form');
    const frontdeskConfirmForm = document.getElementById('frontdesk-confirm-form');
    const frontdeskConfirmButton = document.getElementById('frontdesk-confirm-button');
    const startButton = document.getElementById('start-scanner-button');
    const stopButton = document.getElementById('stop-scanner-button');
    const preview = document.getElementById('scanner-preview');
    const placeholder = document.getElementById('scanner-placeholder');
    const scannerStatus = document.getElementById('scanner-status');
    const lateCountdownCard = document.getElementById('late-countdown-card');
    const lateCountdownText = document.getElementById('late-countdown-text');
    const lateExpiredMessage = document.getElementById('late-expired-message');

    let scanner = null;
    let startPromise = null;
    let stopRequested = false;
    let resultHandled = false;
    let lastRejectedValue = '';
    let lateCountdownInterval = null;
    let videoObserver = null;
    let rawFrameInterval = null;
    let rawScanInFlight = false;
    let qrEnginePromise = null;
    let offscreenCanvas = null;
    let offscreenContext = null;

    const setScannerStatus = (text, tone = 'muted') => {
        if (!scannerStatus) {
            return;
        }

        scannerStatus.textContent = text;
        scannerStatus.classList.remove(
            'text-white/70',
            'text-emerald-300',
            'text-red-300',
            'text-amber-300',
        );
        scannerStatus.classList.add(
            tone === 'ok' ? 'text-emerald-300'
                : tone === 'error' ? 'text-red-300'
                    : tone === 'warn' ? 'text-amber-300'
                        : 'text-white/70',
        );
    };

    const disableFrontdeskConfirmation = () => {
        if (!frontdeskConfirmButton) {
            return;
        }

        frontdeskConfirmButton.disabled = true;
        frontdeskConfirmButton.classList.add('opacity-60', 'pointer-events-none');
        frontdeskConfirmButton.textContent = 'Konfirmasi Sudah Ditutup';
    };

    const formatCountdown = (totalSeconds) => {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return [hours, minutes, seconds]
            .map((value) => String(value).padStart(2, '0'))
            .join(':');
    };

    const startLateCountdown = () => {
        if (!lateCountdownCard || !lateCountdownText) {
            return;
        }

        const deadline = lateCountdownCard.dataset.graceDeadline;
        if (!deadline) {
            return;
        }

        const updateCountdown = () => {
            const remainingSeconds = Math.max(
                0,
                Math.floor((new Date(deadline).getTime() - Date.now()) / 1000),
            );

            lateCountdownText.textContent = formatCountdown(remainingSeconds);

            if (remainingSeconds > 0) {
                return;
            }

            if (lateCountdownInterval) {
                window.clearInterval(lateCountdownInterval);
                lateCountdownInterval = null;
            }

            disableFrontdeskConfirmation();
            lateExpiredMessage?.classList.remove('hidden');
        };

        updateCountdown();
        lateCountdownInterval = window.setInterval(updateCountdown, 1000);
    };

    const fitScannerVideoBox = () => {
        const videoElement = preview?.querySelector('video');

        if (!videoElement || !preview.clientWidth) {
            return;
        }

        videoElement.style.width = '100%';
        videoElement.style.height = `${preview.clientWidth}px`;
        videoElement.style.objectFit = 'cover';
    };

    const observeScannerVideo = () => {
        videoObserver?.disconnect();

        if (!preview || !window.MutationObserver) {
            fitScannerVideoBox();
            return;
        }

        videoObserver = new MutationObserver(() => {
            const videoElement = preview.querySelector('video');

            if (videoElement && !videoElement.dataset.fitHooked) {
                videoElement.dataset.fitHooked = '1';
                fitScannerVideoBox();
                videoElement.addEventListener('loadedmetadata', fitScannerVideoBox, { once: true });
            }
        });

        videoObserver.observe(preview, { childList: true, subtree: true });
        fitScannerVideoBox();
    };

    const stopScanner = async (force = false) => {
        if (startPromise && !force) {
            stopRequested = true;
            setScannerStatus('Kamera akan dihentikan setelah proses pembukaan selesai.');
            return;
        }

        videoObserver?.disconnect();
        videoObserver = null;

        if (rawFrameInterval !== null) {
            window.clearInterval(rawFrameInterval);
            rawFrameInterval = null;
        }
        rawScanInFlight = false;

        const currentScanner = scanner;
        const videoElement = preview?.querySelector('video');
        const tracks = videoElement?.srcObject?.getTracks?.() ?? [];
        let stopPromise = Promise.resolve();

        if (currentScanner?.isScanning) {
            stopPromise = currentScanner.stop().catch((error) => {
                console.warn('Scanner stop membutuhkan pembersihan stream manual:', error);
            });
        }

        tracks.forEach((track) => track.stop());

        let stopTimeout;
        await Promise.race([
            stopPromise,
            new Promise((resolve) => {
                stopTimeout = window.setTimeout(resolve, 1200);
            }),
        ]);
        if (stopTimeout !== undefined) {
            window.clearTimeout(stopTimeout);
        }

        try {
            currentScanner?.clear();
        } catch (error) {
            console.debug('Scanner DOM sudah dibersihkan:', error);
        }

        if (scanner === currentScanner) {
            scanner = null;
        }

        preview?.classList.add('hidden');
        placeholder?.classList.remove('hidden');
    };

    const selectCamera = async () => {
        let cameras = [];

        try {
            cameras = await Html5Qrcode.getCameras();
        } catch (error) {
            console.warn('Camera enumeration unavailable; asking browser to choose a camera:', error);
            return { facingMode: { ideal: 'environment' } };
        }

        const usableCameras = cameras.filter((camera) => !IGNORED_CAMERA_LABEL_PATTERN.test(camera.label || ''));
        const candidateCameras = usableCameras.length ? usableCameras : cameras;
        const preferredCamera = candidateCameras.find((camera) => REAR_CAMERA_LABEL_PATTERN.test(camera.label || ''));

        if (preferredCamera?.id) {
            return preferredCamera.id;
        }

        if (candidateCameras[0]?.id) {
            return candidateCameras[0].id;
        }

        return { facingMode: { ideal: 'environment' } };
    };

    const navigateToReservation = (reservationCode) => {
        const lookupUrl = new URL(frontDeskUrl, window.location.origin);
        lookupUrl.searchParams.set('kode_reservasi', reservationCode);
        window.location.assign(lookupUrl.toString());
    };

    const loadReservation = (reservationCode) => {
        setScannerStatus(`Kode terbaca: ${reservationCode}`, 'ok');
        void stopScanner();
        navigateToReservation(reservationCode);
    };

    const showCameraError = async (error) => {
        const message = cameraErrorMessage(error);
        console.error('Scanner Error:', error);
        setScannerStatus(message, 'error');

        if (placeholder) {
            placeholder.textContent = message;
            placeholder.classList.remove('hidden');
        }

        await stopScanner(true);
        setScannerStatus(message, 'error');
        await Swal.fire({
            icon: 'warning',
            title: 'Kamera Tidak Dapat Diakses',
            text: message,
            confirmButtonColor: '#b91c1c',
        });
    };

    const activateScanner = () => {
        if (!preview || !placeholder || !startButton || startPromise) {
            return startPromise;
        }

        stopRequested = false;
        resultHandled = false;
        lastRejectedValue = '';
        startButton.disabled = true;
        startButton.textContent = 'Menyiapkan Kamera…';
        setScannerStatus('Mendeteksi kamera yang tersedia…');
        placeholder.textContent = 'Menyiapkan kamera. Izinkan akses jika browser memintanya.';
        preview.classList.remove('hidden');
        placeholder.classList.remove('hidden');

        startPromise = (async () => {
            await stopScanner(true);
            preview.classList.remove('hidden');
            placeholder.classList.remove('hidden');
            placeholder.textContent = 'Menyiapkan kamera. Izinkan akses jika browser memintanya.';

            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
                throw new Error('Akses kamera membutuhkan HTTPS atau alamat localhost.');
            }

            const selectedCamera = await selectCamera();
            scanner = new Html5Qrcode('scanner-preview', {
                verbose: false,
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                useBarCodeDetectorIfSupported: false,
            });

            const config = {
                fps: 5,
                qrbox: (width, height) => {
                    const side = Math.floor(Math.min(width, height) * 0.8);

                    return { width: side, height: side };
                },
                videoConstraints: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            };

            observeScannerVideo();
            setScannerStatus('Meminta izin kamera…');

            const handleDecodedText = (decodedText) => {
                const scannedValue = (decodedText || '').trim().toUpperCase();

                if (!scannedValue || resultHandled) {
                    return;
                }

                if (!RESERVATION_CODE_PATTERN.test(scannedValue)) {
                    if (scannedValue !== lastRejectedValue) {
                        lastRejectedValue = scannedValue;
                        setScannerStatus(
                            `Terbaca, tetapi bukan kode reservasi: ${scannedValue.slice(0, 40)}`,
                            'warn',
                        );
                    }

                    return;
                }

                resultHandled = true;
                if (codeInput) {
                    codeInput.value = scannedValue;
                }
                loadReservation(scannedValue);
            };

            const decodeCanvasRegion = (videoElement, sx, sy, sw, sh, targetWidth, targetHeight) => {
                if (offscreenCanvas.width !== targetWidth || offscreenCanvas.height !== targetHeight) {
                    offscreenCanvas.width = targetWidth;
                    offscreenCanvas.height = targetHeight;
                }

                offscreenContext.drawImage(videoElement, sx, sy, sw, sh, 0, 0, targetWidth, targetHeight);
                const imageData = offscreenContext.getImageData(0, 0, targetWidth, targetHeight);

                return jsQR(imageData.data, targetWidth, targetHeight, {
                    inversionAttempts: 'attemptBoth',
                });
            };

            const getQrEngine = () => {
                if (!qrEnginePromise) {
                    // Force Nimiq's Web Worker decoder so Windows BarcodeDetector bugs are bypassed.
                    QrScanner._disableBarcodeDetector = true;
                    qrEnginePromise = QrScanner.createQrEngine().catch((error) => {
                        console.warn('QrScanner worker unavailable, using jsQR fallback:', error);
                        return null;
                    });
                }

                return qrEnginePromise;
            };

            const startRawFrameFallback = () => {
                if (rawFrameInterval !== null) {
                    window.clearInterval(rawFrameInterval);
                }

                if (!offscreenCanvas) {
                    offscreenCanvas = document.createElement('canvas');
                    offscreenContext = offscreenCanvas.getContext('2d', { willReadFrequently: true });
                }

                const enginePromise = getQrEngine();

                rawFrameInterval = window.setInterval(async () => {
                    if (resultHandled || rawScanInFlight) {
                        return;
                    }

                    const videoElement = preview?.querySelector('video');
                    if (!videoElement || videoElement.readyState < 2 || !videoElement.videoWidth || !videoElement.videoHeight) {
                        return;
                    }

                    rawScanInFlight = true;
                    try {
                        const qrEngine = await enginePromise;
                        const vw = videoElement.videoWidth;
                        const vh = videoElement.videoHeight;
                        const side = Math.min(vw, vh);
                        const sx = Math.floor((vw - side) / 2);
                        const sy = Math.floor((vh - side) / 2);

                        if (qrEngine) {
                            try {
                                const result = await QrScanner.scanImage(videoElement, {
                                    qrEngine,
                                    scanRegion: {
                                        x: sx,
                                        y: sy,
                                        width: side,
                                        height: side,
                                        downScaledWidth: 480,
                                        downScaledHeight: 480,
                                    },
                                    alsoTryWithoutScanRegion: true,
                                    returnDetailedScanResult: true,
                                });

                                if (result?.data) {
                                    handleDecodedText(result.data);
                                    return;
                                }
                            } catch {
                                // No QR code found in this frame; try next frame or fallback.
                            }
                        } else if (offscreenContext) {
                            const targetSide = Math.min(side, 560);
                            const decoded = decodeCanvasRegion(videoElement, sx, sy, side, side, targetSide, targetSide);
                            if (decoded?.data) {
                                handleDecodedText(decoded.data);
                            }
                        }
                    } finally {
                        rawScanInFlight = false;
                    }
                }, 180);
            };

            await scanner.start(
                selectedCamera,
                config,
                handleDecodedText,
            );

            if (stopRequested) {
                await stopScanner(true);
                setScannerStatus('Kamera dihentikan. Tekan tombol untuk memulai lagi.');
                return;
            }

            placeholder.classList.add('hidden');
            fitScannerVideoBox();
            startRawFrameFallback();
            setScannerStatus('Kamera siap. Arahkan QR ke tengah frame.', 'ok');
        })();

        return startPromise
            .catch(showCameraError)
            .finally(() => {
                startPromise = null;
                startButton.disabled = false;
                startButton.textContent = 'Mulai / Ulangi Kamera';
            });
    };

    if (successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Tamu Berhasil Dikonfirmasi',
            text: successMessage,
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: false,
            confirmButtonColor: '#b91c1c',
        }).then(() => {
            window.location.assign(frontDeskUrl);
        });
    }

    if (errorMessage) {
        Swal.fire({
            icon: 'error',
            title: 'Konfirmasi Ditolak',
            text: errorMessage,
            confirmButtonColor: '#b91c1c',
        });
    }

    frontdeskConfirmForm?.addEventListener('submit', async (event) => {
        if (frontdeskConfirmButton?.disabled) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        const result = await Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Kehadiran?',
            text: 'Pastikan tamu yang hadir sudah sesuai dengan data reservasi yang tampil.',
            showCancelButton: true,
            confirmButtonText: 'Ya, konfirmasi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#6b7280',
        });

        if (result.isConfirmed) {
            frontdeskConfirmForm.submit();
        }
    });

    startLateCountdown();

    if (!startButton || !stopButton || !preview || !placeholder || !codeInput || !manualConfirmForm) {
        return;
    }

    stopButton.addEventListener('click', async () => {
        if (startPromise) {
            stopRequested = true;
            setScannerStatus('Kamera akan dihentikan setelah proses pembukaan selesai.');
            return;
        }

        await stopScanner(true);
        setScannerStatus('Kamera dihentikan. Tekan tombol untuk memulai lagi.');
    });

    startButton.addEventListener('click', () => {
        void activateScanner();
    });

    manualConfirmForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const manualCode = codeInput.value.trim().toUpperCase();
        if (!manualCode) {
            await Swal.fire({
                icon: 'warning',
                title: 'Kode Reservasi Kosong',
                text: 'Masukkan kode reservasi terlebih dahulu sebelum melakukan konfirmasi manual.',
                confirmButtonColor: '#b91c1c',
            });
            return;
        }

        loadReservation(manualCode);
    });

    window.addEventListener('resize', fitScannerVideoBox);
    window.addEventListener('pagehide', () => {
        void stopScanner(true);
    }, { once: true });

    if (!new URLSearchParams(window.location.search).get('kode_reservasi')) {
        void activateScanner();
    } else {
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        placeholder.textContent = 'Kode sudah dimuat. Tekan tombol untuk memindai reservasi lain.';
        setScannerStatus('Kode reservasi sudah dimuat.', 'ok');
    }
}
