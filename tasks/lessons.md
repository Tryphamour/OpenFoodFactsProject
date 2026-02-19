# Lessons Learned

This file stores prevention rules after failures or corrections.

- Never commit `.env` or `.env.dev`; keep only `.env.example` under version control.
- When introducing `config/services_test.yaml`, preserve deterministic test bindings (notably fixed 2FA code generator) to avoid functional auth flakiness.
- When running PHPUnit inside Docker, force `APP_ENV=test` (for example `docker compose exec -T -e APP_ENV=test frankenphp php vendor/bin/simple-phpunit`) so `WebTestCase` gets `test.service_container`.
- When `APP_ENV=test` runs with `debug=false`, clear test cache after adding routes/services to avoid stale container metadata (`docker compose exec -T -e APP_ENV=test frankenphp php bin/console cache:clear`).
- In demo/prod profile, clear cache before warmup after Twig/UI changes (`php bin/console cache:clear --env=prod --no-debug`) to avoid stale rendered dashboard actions.
