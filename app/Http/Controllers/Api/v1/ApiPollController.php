<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()->orderBy('created_at', 'desc')->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    /**
     * Remove the specified poll.
     */
    public function remove(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();

        return response()->json(['message' => 'success'], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'is_draft' => 'boolean',
            'allow_multiple' => 'boolean',
            'results_public' => 'boolean',
            'ends_at' => 'nullable|date|after:now',
        ]);

        // Générer un token unique
        $data['secret_token'] = \Illuminate\Support\Str::random(32);
        $data['user_id'] = $request->user()->id;

        $poll = Poll::create($data);

        return response()->json($poll->load('options'), 201);
    }

    public function update(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $data = $request->validate([
            'question' => 'sometimes|string|max:255',
            'title' => 'nullable|string|max:255',
            'is_draft' => 'boolean',
            'allow_multiple' => 'boolean',
            'results_public' => 'boolean',
            'ends_at' => 'nullable|date',
            'started_at' => 'nullable|date',
        ]);

        $poll->update($data);

        return response()->json($poll->load('options'));
    }

    public function storeOption(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $data = $request->validate(['label' => 'required|string|max:255']);
        $option = $poll->options()->create($data);

        return response()->json($option, 201);
    }

    public function destroyOption(Request $request, int $id, int $optionId)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $option = $poll->options()->where('id', $optionId)->first();
        if (! $option) {
            return response()->json(['message' => 'Option not found.'], 404);
        }

        $option->delete();

        return response()->json(null, 204);
    }

    public function vote(Request $request, int $id)
    {
        $poll = Poll::with('options')->where('id', $id)->first();
        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }
        if ($poll->is_draft) {
            return response()->json(['message' => 'Poll not started.'], 403);
        }

        if ($poll->ends_at && now()->isAfter($poll->ends_at)) {
            return response()->json(['message' => 'Poll has ended.'], 403);
        }

        $data = $request->validate([
            'option_ids' => 'required|array|min:1',
            'option_ids.*' => 'integer|exists:poll_options,id',
        ]);

        // Choix unique : refuser si plusieurs options envoyées
        if (! $poll->allow_multiple && count($data['option_ids']) > 1) {
            return response()->json(['message' => 'Only one choice allowed.'], 422);
        }

        // Vérifier vote déjà existant
        $pollOptionIds = $poll->options->pluck('id');
        $alreadyVoted = PollVote::where('user_id', $request->user()->id)
            ->whereIn('poll_option_id', $pollOptionIds)
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['message' => 'Already voted.'], 409);
        }

        foreach ($data['option_ids'] as $optionId) {
            // Vérifier que l'option appartient bien à ce poll
            if (! $pollOptionIds->contains($optionId)) {
                continue;
            }
            PollVote::create(['user_id' => $request->user()->id, 'poll_option_id' => $optionId]);
        }

        return response()->json(['message' => 'Vote recorded.'], 201);
    }

    public function results(Request $request, int $id)
    {
        $poll = Poll::with(['options' => function ($q) {
            $q->withCount('votes');
        }])->where('id', $id)->first();

        if (! $poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $user = $request->user(); // peut être null (route publique)
        $isOwner = $user && $user->id === $poll->user_id;

        if (! $poll->results_public && ! $isOwner) {
            return response()->json(['message' => 'Results are private.'], 403);
        }

        return response()->json($poll->options->map(fn ($opt) => [
            'id' => $opt->id,
            'label' => $opt->label,
            'votes_count' => $opt->votes_count,
        ]));
    }
}
