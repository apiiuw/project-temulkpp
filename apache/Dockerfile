FROM apache/superset:6.1.0

USER root
RUN uv pip install --python /app/.venv/bin/python --no-cache pymysql==1.1.1
USER superset

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=5 \
    CMD /app/.venv/bin/python -c "import urllib.request; urllib.request.urlopen('http://127.0.0.1:8088/health', timeout=4)"
