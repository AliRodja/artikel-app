@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="{{ route('home') }}" class="link text-sm">← Kembali ke daftar artikel</a>

        <h1 class="mt-4 text-3xl leading-tight font-bold text-gray-900">{{ $article->title }}</h1>
        <p class="mt-2 text-sm text-gray-500">
            Oleh <span class="font-medium text-gray-700">{{ $article->user?->name ?? 'Anonim' }}</span>
            · {{ $article->created_at->format('d M Y') }}
        </p>

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mt-6 w-full rounded-xl object-cover">
        @endif

        <div class="card article-content mt-6 p-6 sm:p-8">{{ $article->content }}</div>
    </article>
@endsection
