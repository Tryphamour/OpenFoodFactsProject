# Lessons Learned

This file stores prevention rules after failures or corrections.

- Never commit `.env` or `.env.dev`; keep only `.env.example` under version control.
- When introducing `config/services_test.yaml`, preserve deterministic test bindings (notably fixed 2FA code generator) to avoid functional auth flakiness.
