# Payment Request API Documentation

## Base URL

```text
http://localhost:8000/api
```

---

# Authentication

All endpoints require authentication.

Header:

```http
Authorization: Bearer {token}
```


```md
# Login

## Request

POST /api/login

### Body

```json
{
  "email": "admin@example.com",
  "password": "12345678"
}
---

# Create Payment Request

Create a new payment request.

## Request

**Method**

```http
POST
```

**URL**

```http
/api/payment-requests
```

## Request Parameters

| Parameter    | Type   | Required | Description         |
| ------------ | ------ | -------- | ------------------- |
| description  | string | Yes      | Payment description |
| amount_local | number | Yes      | Original amount     |
| currency     | string | Yes      | Currency code       |

## Example Request

```json
{
  "description": "Payment for consulting services",
  "amount_local": 1000,
  "currency": "USD"
}
```

## Example Response (201)

```json
{
  "message": "Payment request created successfully",
  "data": {
    "id": "uuid",
    "description": "Payment for consulting services",
    "amount_local": 1000,
    "currency": "USD",
    "amount_eur": 850,
    "exchange_rate": 0.85,
    "status": "pending"
  }
}
```

---

# List Payment Requests

Retrieve payment requests.

## Request

**Method**

```http
GET
```

**URL**

```http
/ api/payment-requests
```

## Example Response (200)

```json
{
  "data": [
    {
      "id": "uuid",
      "description": "Payment for consulting services",
      "amount_local": 1000,
      "currency": "USD",
      "amount_eur": 850,
      "status": "pending"
    }
  ],

  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 15,
    "total": 16
  }
}
```

---

# Update Payment Request Status

Update the status of a payment request.

Only Finance Admin users are allowed.

## Request

**Method**

```http
PUT
```

**URL**

```http
/ api/payment-requests/{id}/status
```

## Request Parameters

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| status    | string | Yes      | New status  |

## Allowed Status

* pending
* approved
* rejected
* expired

## Example Request

```json
{
  "status": "approved"
}
```

## Example Response (200)

```json
{
  "message": "Payment request status updated successfully",

  "data": {
    "id": "uuid",
    "status": "approved"
  }
}
```

---

# Error Responses

## Validation Error (422)

```json
{
  "message": "The given data was invalid.",

  "errors": {
    "currency": [
      "The selected currency is invalid."
    ]
  }
}
```

## Unauthorized (401)

```json
{
  "message": "Unauthenticated."
}
```

## Forbidden (403)

```json
{
  "message": "Only finance admins can update payment requests"
}
```


