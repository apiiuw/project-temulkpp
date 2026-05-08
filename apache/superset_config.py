import os
import pymysql

DATA_DIR = os.path.expanduser("~/.superset")
SQLALCHEMY_DATABASE_URI = f"sqlite:///{os.path.join(DATA_DIR, 'superset.db')}"
SQLALCHEMY_ENGINE_OPTIONS = {"connect_args": {"timeout": 30}}
SQLALCHEMY_TRACK_MODIFICATIONS = False

pymysql.install_as_MySQLdb()

from superset.security import SupersetSecurityManager
from superset.exceptions import SupersetSecurityException

class CustomSecurityManager(SupersetSecurityManager):
    def raise_for_access(self, *args, **kwargs) -> None:
        try:
            super().raise_for_access(*args, **kwargs)
        except SupersetSecurityException as e:
            if "Guest user cannot modify chart payload" in str(e):
                return
            raise e

CUSTOM_SECURITY_MANAGER = CustomSecurityManager

SECRET_KEY = "mlYavpf15M2Wcx1vPhTSm7Prqa4oZafSR6+LLMRrBs+GY4J7Bf8HO/Li"
GUEST_TOKEN_JWT_SECRET = "BnT1KbQj3K2tJUKQ2cwhSZlJmTbRaVC8yCcZzdj6dLOBRKYrRJBG7HJf"

FEATURE_FLAGS = {
    "EMBEDDED_SUPERSET": True,
    "DASHBOARD_NATIVE_FILTERS": True,
    "DASHBOARD_NATIVE_FILTERS_SET": True,
    "DASHBOARD_RBAC": True,
    "EMBEDDABLE_CHARTS": True,
}

ENABLE_ROW_LEVEL_SECURITY = True

GUEST_ROLE_NAME = "Admin"
PUBLIC_ROLE_LIKE = "Admin"
AUTH_ROLE_PUBLIC = "Admin"
GUEST_TOKEN_JWT_EXP_SECONDS = 3600
ALLOW_DASHBOARD_EXPORT_FOR_GUESTS = True
LOG_LEVEL = "DEBUG"

PREVENT_UNSAFE_DB_CONNECTIONS = False

ENABLE_CORS = True
CORS_OPTIONS = {
    "supports_credentials": True,
    "allow_headers": ["*"],
    "resources": ["*"],
    "origins": ["http://localhost:8000"],
}

TALISMAN_ENABLED = True
TALISMAN_CONFIG = {
    "content_security_policy": {
        "base-uri": ["'self'"],
        "default-src": ["'self'"],
        "img-src": ["'self'", "blob:", "data:"],
        "worker-src": ["'self'", "blob:"],
        "connect-src": [
            "'self'",
            "http://localhost:8088",
            "http://localhost:8000",
            "https://api.mapbox.com",
            "https://events.mapbox.com",
        ],
        "object-src": ["'none'"],
        "style-src": ["'self'", "'unsafe-inline'"],
        "script-src": ["'self'", "'unsafe-inline'", "'unsafe-eval'"],
        "frame-ancestors": ["http://localhost:8000"],
    },
    "content_security_policy_nonce_in": ["script-src"],
    "force_https": False,
    "session_cookie_secure": False,
}

SESSION_COOKIE_SAMESITE = "Lax"
SESSION_COOKIE_SECURE = False
SESSION_COOKIE_HTTPONLY = True

WTF_CSRF_ENABLED = False
WTF_CSRF_METHODS = []

ENABLE_PROXY_FIX = True

AUTH_ROLE_PUBLIC = 'Public'

GUEST_TOKEN_JWT_ALGO = 'HS256'