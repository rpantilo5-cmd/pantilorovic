# Submission

- Name: Rovic Pantilo
- Section: [Your section]
- GitHub repository URL: [Your repository URL]

## Postman evidence checklist

- [ ] `evidence/01-public-api-200.png`
- [ ] `evidence/02-register-201.png`
- [ ] `evidence/03-login-200.png`
- [ ] `evidence/04-profile-no-token-401.png`
- [ ] `evidence/05-profile-with-token-200.png`
- [ ] `evidence/06-invalid-login-422.png`
- [ ] `evidence/07-logout-200.png`
- [ ] `evidence/08-revoked-token-401.png`

Redact tokens and do not show passwords, `.env` values, or other secrets in screenshots.

## Reflection

A 401 response means the protected endpoint did not receive a valid authentication token; a successful profile response returns the authenticated user's public profile. Logging out revokes the current token, so requests using it are unauthorized afterward.
