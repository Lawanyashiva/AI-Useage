<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenAI\OpenAI;

class NoteController extends Controller
{
    protected OpenAI $openai;
    protected string $chatModel;
    protected string $embeddingModel;

    public function __construct()
    {
        $this->openai = OpenAI::client(config('openai.api_key'));
        $this->chatModel = config('openai.chat_model');
        $this->embeddingModel = config('openai.embedding_model');
    }

    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $notes = Note::orderBy('updated_at', 'desc')
            ->paginate($limit);

        return response()->json($notes);
    }

    public function show($id)
    {
        $note = Note::find($id);

        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        return response()->json($note);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['embedding'] = $this->generateEmbedding($data['body']);

        $note = Note::create($data);

        return response()->json($note, 201);
    }

    public function update(Request $request, $id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note->fill($validator->validated());

        if ($request->filled('body')) {
            $note->embedding = $this->generateEmbedding($note->body);
        }

        $note->save();

        return response()->json($note);
    }

    public function destroy($id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $note->delete();
        return response()->json(['message' => 'Note deleted']);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = $request->input('query');
        $limit = $request->input('limit', 10);
        $queryEmbedding = $this->generateEmbedding($query);

        $notes = Note::whereNotNull('embedding')->get();
        $scores = $notes->map(function (Note $note) use ($queryEmbedding) {
            return [
                'note' => $note,
                'score' => $this->cosineSimilarity($queryEmbedding, $note->embedding ?? []),
            ];
        })->sortByDesc('score')
          ->take($limit)
          ->values()
          ->map(fn ($item) => array_merge(['score' => $item['score']], $item['note']->toArray()));

        return response()->json(['data' => $scores]);
    }

    public function summary($id)
    {
        $note = Note::find($id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        $prompt = "Summarize the following note content in 2-3 sentences:\n\n" . $note->body;

        $response = $this->openai->chat->create([
            'model' => $this->chatModel,
            'messages' => [
                ['role' => 'system', 'content' => 'You are an AI note summarizer.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 150,
        ]);

        $summary = trim($response->choices[0]->message->content ?? '');

        return response()->json(['summary' => $summary]);
    }

    protected function generateEmbedding(string $text): array
    {
        $result = $this->openai->embeddings->create([
            'model' => $this->embeddingModel,
            'input' => $text,
        ]);

        return $result->data[0]->embedding ?? [];
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) === 0 || count($b) === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $dot += $value * ($b[$i] ?? 0);
            $normA += $value * $value;
            $normB += ($b[$i] ?? 0) * ($b[$i] ?? 0);
        }

        return $normA > 0 && $normB > 0 ? $dot / (sqrt($normA) * sqrt($normB)) : 0.0;
    }
}
