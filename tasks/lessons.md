# Lessons Learned

This file stores prevention rules after failures or corrections.

- Never commit `.env` or `.env.dev`; keep only `.env.example` under version control.
- When introducing `config/services_test.yaml`, preserve deterministic test bindings (notably fixed 2FA code generator) to avoid functional auth flakiness.
- When running PHPUnit inside Docker, force `APP_ENV=test` (for example `docker compose exec -T -e APP_ENV=test frankenphp php vendor/bin/simple-phpunit`) so `WebTestCase` gets `test.service_container`.
