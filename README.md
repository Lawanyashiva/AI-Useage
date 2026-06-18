# AI-powered Notes Management System

A Laravel-based Notes API with AI-powered semantic search and note summarization.

## Features
- Notes CRUD API
- Pagination
- AI semantic search powered by OpenAI embeddings
- AI-generated note summaries
- Simple mobile-friendly frontend UI
- Docker support for local development

## Setup Instructions
1. Copy example environment file:
   ```bash
   cp .env.example .env
   ```
2. Install composer dependencies:
   ```bash
   composer install
   ```
3. Run unit tests (after dependencies are installed):
   ```bash
   vendor/bin/phpunit
   ```
3. Add your database configuration to `.env`.
4. Add your OpenAI API key to `.env`:
   ```env
   OPENAI_API_KEY=your_api_key_here
   ```
5. Generate the application key:
   ```bash
   php artisan key:generate
   ```
6. Run the database migration:
   ```bash
   php artisan migrate
   ```
7. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

## API Documentation
- `GET /api/notes?page=1&limit=10` - List notes with pagination
- `POST /api/notes` - Create a new note
- `GET /api/notes/{id}` - Retrieve a note
- `PUT /api/notes/{id}` - Update a note
- `DELETE /api/notes/{id}` - Delete a note
- `GET /api/notes/search?query=...` - Perform semantic search
- `POST /api/notes/{id}/summary` - Generate AI summary of a note

### Request body for create/update
```json
{
  "title": "Meeting notes",
  "body": "Discuss project deadlines and next milestones."
}
```

## AI Tools Used
- OpenAI API via `openai/openai`
- Embeddings model: `text-embedding-3-small`
- Chat model: `gpt-3.5-turbo`

## AI Usage Explanation
AI is used to generate note embeddings on create/update. The semantic search endpoint compares query embeddings with stored note embeddings to retrieve relevant notes. The summary endpoint uses a chat model to generate concise summaries of note content.

## Architecture
- Laravel application with RESTful API routes
- Notes stored in MySQL with a JSON embedding field
- AI integration in controller logic for embeddings and summaries
- Simple frontend powered by native JavaScript and responsive CSS

## Docker Support
Run with Docker Compose:
```bash
docker compose up -d --build
``` 

Then run migrations inside the container:
```bash
docker compose exec app php artisan migrate
```
