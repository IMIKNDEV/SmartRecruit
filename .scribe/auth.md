# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

SmartRecruit uses Laravel Sanctum bearer tokens. Authenticate by calling <code>POST /api/login</code> with your email/password, then send the returned token in the <code>Authorization: Bearer &lt;token&gt;</code> header. Recruiter-only and candidate-only endpoints are enforced by role.
