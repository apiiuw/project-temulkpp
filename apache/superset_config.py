SECRET_KEY = "mlYavpf15M2Wcx1vPhTSm7Prqa4oZafSR6+LLMRrBs+GY4J7Bf8HO/Li"
PREVENT_UNSAFE_DB_CONNECTIONS = False
GUEST_TOKEN_JWT_SECRET = "BnT1KbQj3K2tJUKQ2cwhSZlJmTbRaVC8yCcZzdj6dLOBRKYrRJBG7HJf"

import pymysql
pymysql.install_as_MySQLdb()

FEATURE_FLAGS = {
    "EMBEDDED_SUPERSET": True
}

# Allow CORS for the Laravel app
ENABLE_CORS = True
CORS_OPTIONS = {
    'supports_credentials': True,
    'allow_headers': ['*'],
    'resources': ['*'],
    'origins': ['http://localhost:8000']
}

# Talisman security settings for embedding
TALISMAN_CONFIG = {
    "content_security_policy": {
        "base-uri": ["'self'"],
        "default-src": ["'self'"],
        "img-src": ["'self'", "blob:", "data:"],
        "worker-src": ["'self'", "blob:"],
        "connect-src": [
            "'self'",
            "https://api.mapbox.com",
            "https://events.mapbox.com",
        ],
        "object-src": ["'none'"],
        "style-src": [
            "'self'",
            "'unsafe-inline'",
        ],
        "script-src": ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        "frame-ancestors": ["http://localhost:8000"],
    },
    "content_security_policy_nonce_in": ["script-src"],
    "force_https": False,
    "session_cookie_secure": False,
}

# Cookie settings for cross-port development
SESSION_COOKIE_SAMESITE = "Lax"
SESSION_COOKIE_SECURE = False
SESSION_COOKIE_HTTPONLY = True

# Disable CSRF for API access (required for backend-to-backend guest token requests)
WTF_CSRF_ENABLED = False
WTF_CSRF_METHODS = []

# Define the role for guest users
GUEST_ROLE_NAME = "Public"