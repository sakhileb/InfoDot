# API Documentation

InfoDot provides a RESTful API for programmatic access to platform features. All API endpoints require authentication using Laravel Sanctum tokens.

## Base URL

```
Production: https://infodot.com/api
Staging: https://staging.infodot.com/api
Local: http://localhost:8000/api
```

## Authentication

### Generate API Token

**Endpoint**: `POST /api/tokens/create`

**Request**:
```json
{
  "email": "user@example.com",
  "password": "password",
  "device_name": "My Device"
}
```

**Response**:
```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  }
}
```

### Using API Token

Include the token in the `Authorization` header:

```bash
Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz1234567890
```

### Revoke Token

**Endpoint**: `POST /api/tokens/revoke`

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "message": "Token revoked successfully"
}
```

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **Authenticated requests**: 60 requests per minute
- **Unauthenticated requests**: 10 requests per minute

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1642089600
```

## Error Responses

All errors follow a consistent format:

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

### HTTP Status Codes

- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `204 No Content`: Request successful, no content to return
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

## Endpoints

### User

#### Get Authenticated User

**Endpoint**: `GET /api/user`

**Headers**:
```
Authorization: Bearer {token}
```

**Response**:
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "profile_photo_url": "https://example.com/photo.jpg",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-01-15T00:00:00.000000Z"
  }
}
```

### Answers

#### Get Answers for Question

**Endpoint**: `GET /api/answers/question/{questionId}`

**Headers**:
```
Authorization: Bearer {token}
```

**Parameters**:
- `questionId` (integer, required): Question ID

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "content": "This is an answer",
      "is_accepted": false,
      "user": {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      "likes_count": 5,
      "dislikes_count": 1,
      "comments_count": 3,
      "created_at": "2026-01-10T00:00:00.000000Z",
      "updated_at": "2026-01-15T00:00:00.000000Z"
    }
  ]
}
```

#### Create Answer

**Endpoint**: `POST /api/answers`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request**:
```json
{
  "question_id": 1,
  "content": "This is my answer to the question."
}
```

**Validation Rules**:
- `question_id`: required, integer, exists in questions table
- `content`: required, string, minimum 1 character

**Response** (201 Created):
```json
{
  "data": {
    "id": 2,
    "content": "This is my answer to the question.",
    "is_accepted": false,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "likes_count": 0,
    "dislikes_count": 0,
    "comments_count": 0,
    "created_at": "2026-01-15T12:00:00.000000Z",
    "updated_at": "2026-01-15T12:00:00.000000Z"
  }
}
```

#### Delete Answer

**Endpoint**: `DELETE /api/answers/{id}`

**Headers**:
```
Authorization: Bearer {token}
```

**Parameters**:
- `id` (integer, required): Answer ID

**Authorization**: Only the answer author can delete their answer

**Response** (204 No Content)

#### Toggle Like/Dislike

**Endpoint**: `POST /api/answers/{id}/like`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parameters**:
- `id` (integer, required): Answer ID

**Request**:
```json
{
  "like": true
}
```

**Request Body**:
- `like` (boolean, required): `true` for like, `false` for dislike

**Response**:
```json
{
  "data": {
    "id": 1,
    "content": "This is an answer",
    "is_accepted": false,
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "likes_count": 6,
    "dislikes_count": 1,
    "comments_count": 3,
    "user_liked": true,
    "user_disliked": false,
    "created_at": "2026-01-10T00:00:00.000000Z",
    "updated_at": "2026-01-15T00:00:00.000000Z"
  }
}
```

#### Add Comment

**Endpoint**: `POST /api/answers/{id}/comments`

**Headers**:
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Parameters**:
- `id` (integer, required): Answer ID

**Request**:
```json
{
  "body": "This is a comment on the answer."
}
```

**Validation Rules**:
- `body`: required, string, minimum 1 character, maximum 1000 characters

**Response** (201 Created):
```json
{
  "data": {
    "id": 1,
    "body": "This is a comment on the answer.",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com"
    },
    "created_at": "2026-01-15T12:00:00.000000Z",
    "updated_at": "2026-01-15T12:00:00.000000Z"
  }
}
```

#### Get Comments

**Endpoint**: `GET /api/answers/{id}/comments`

**Headers**:
```
Authorization: Bearer {token}
```

**Parameters**:
- `id` (integer, required): Answer ID

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "body": "This is a comment on the answer.",
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
      },
      "created_at": "2026-01-15T12:00:00.000000Z",
      "updated_at": "2026-01-15T12:00:00.000000Z"
    }
  ]
}
```

#### Toggle Answer Acceptance

**Endpoint**: `POST /api/answers/{id}/accept`

**Headers**:
```
Authorization: Bearer {token}
```

**Parameters**:
- `id` (integer, required): Answer ID

**Authorization**: Only the question author can accept answers

**Response**:
```json
{
  "data": {
    "id": 1,
    "content": "This is an answer",
    "is_accepted": true,
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "likes_count": 6,
    "dislikes_count": 1,
    "comments_count": 3,
    "created_at": "2026-01-10T00:00:00.000000Z",
    "updated_at": "2026-01-15T00:00:00.000000Z"
  }
}
```

**Note**: Accepting an answer automatically unaccepts any previously accepted answer for that question.

## Pagination

List endpoints support pagination using Laravel's standard pagination:

**Query Parameters**:
- `page` (integer, optional): Page number (default: 1)
- `per_page` (integer, optional): Items per page (default: 15, max: 100)

**Example Request**:
```
GET /api/answers/question/1?page=2&per_page=20
```

**Paginated Response**:
```json
{
  "data": [...],
  "links": {
    "first": "http://example.com/api/answers/question/1?page=1",
    "last": "http://example.com/api/answers/question/1?page=5",
    "prev": "http://example.com/api/answers/question/1?page=1",
    "next": "http://example.com/api/answers/question/1?page=3"
  },
  "meta": {
    "current_page": 2,
    "from": 16,
    "last_page": 5,
    "path": "http://example.com/api/answers/question/1",
    "per_page": 15,
    "to": 30,
    "total": 75
  }
}
```

## Filtering and Sorting

Some endpoints support filtering and sorting:

**Query Parameters**:
- `sort` (string, optional): Field to sort by
- `order` (string, optional): Sort order (`asc` or `desc`)
- `filter[field]` (string, optional): Filter by field value

**Example Request**:
```
GET /api/answers/question/1?sort=created_at&order=desc&filter[is_accepted]=true
```

## Webhooks

InfoDot can send webhooks for certain events. Configure webhooks in your account settings.

### Supported Events

- `question.created`: New question created
- `answer.created`: New answer posted
- `answer.accepted`: Answer accepted
- `comment.created`: New comment added

### Webhook Payload

```json
{
  "event": "answer.created",
  "timestamp": "2026-01-15T12:00:00.000000Z",
  "data": {
    "id": 1,
    "question_id": 1,
    "content": "This is an answer",
    "user": {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    }
  }
}
```

### Webhook Signature

Webhooks include a signature header for verification:

```
X-Webhook-Signature: sha256=abcdef1234567890...
```

Verify signature:

```php
$payload = file_get_contents('php://input');
$signature = hash_hmac('sha256', $payload, config('app.webhook_secret'));

if (!hash_equals($signature, $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'])) {
    abort(403, 'Invalid signature');
}
```

## Code Examples

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://infodot.com/api/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

// Get user
$response = $client->get('user');
$user = json_decode($response->getBody(), true);

// Create answer
$response = $client->post('answers', [
    'json' => [
        'question_id' => 1,
        'content' => 'This is my answer'
    ]
]);
$answer = json_decode($response->getBody(), true);
```

### JavaScript (Fetch)

```javascript
const token = 'your-api-token';
const baseUrl = 'https://infodot.com/api';

// Get user
fetch(`${baseUrl}/user`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));

// Create answer
fetch(`${baseUrl}/answers`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    question_id: 1,
    content: 'This is my answer'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### Python (Requests)

```python
import requests

token = 'your-api-token'
base_url = 'https://infodot.com/api'
headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

# Get user
response = requests.get(f'{base_url}/user', headers=headers)
user = response.json()

# Create answer
response = requests.post(
    f'{base_url}/answers',
    headers=headers,
    json={
        'question_id': 1,
        'content': 'This is my answer'
    }
)
answer = response.json()
```

### cURL

```bash
# Get user
curl -X GET https://infodot.com/api/user \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json"

# Create answer
curl -X POST https://infodot.com/api/answers \
  -H "Authorization: Bearer your-api-token" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "question_id": 1,
    "content": "This is my answer"
  }'
```

## Testing

Use the following test credentials in staging environment:

```
Email: test@infodot.com
Password: password
```

## Support

For API support:
- **Email**: api-support@infodot.com
- **Documentation**: https://docs.infodot.com
- **Status Page**: https://status.infodot.com

## Changelog

### Version 2.0.0 (2026-01-15)
- Migrated to Laravel 11
- Updated authentication to Sanctum 4.x
- Improved error responses
- Added rate limiting
- Enhanced security

### Version 1.0.0 (2024-01-01)
- Initial API release
- Basic CRUD operations for answers
- User authentication

---

**API Version**: 2.0.0  
**Last Updated**: January 15, 2026
