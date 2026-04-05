# MART API - Werkvertrag TODO

Source: Email from Weizenbaum Institute (feedback from Stefan Flaschko)

API Documentation: [DOCS/MART/API.md](DOCS/MART/API.md)

---

## 1. Documentation Gaps

Missing details in API docs for the following endpoints:

- [ ] `POST /api/mart/send-password-setup` - add request/response format
- [ ] `POST /api/mart/refresh` - add request/response format
- [ ] `POST /mart-api/cases/{id}/files` - add request/response format

---

## 2. Endpoints Returning Errors

### 403 Errors (Forbidden)

- [ ] `POST /mart-api/device-infos` - returns 403
- [ ] `POST /mart-api/stats` - returns 403

### 302 Errors (Redirect - likely auth/middleware issue)

- [ ] `POST /api/mart/check-password` - returns 302
- [ ] `POST /api/mart/check-access` - returns 302
- [ ] `POST /api/mart/refresh` - returns 302
- [ ] `POST /mart-api/cases/{id}/files` - returns 302

---

## 3. File Upload Endpoint Spec

`POST /mart-api/cases/{id}/files`

**Request:**
```ts
export type uploadFileRequest = {
  file: File;
};
```

**Expected Response:**
```ts
export type uploadFileResponse = {
  fileUrl: string;
};
```

---

## Notes

- Fixing only documentation is not enough; the endpoints themselves must work.
- Scope covers **all** API-related issues, not just login/authentication.
