@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Artikel Terbaru</h1>
            <p class="text-sm text-gray-500">Kumpulan artikel yang telah dipublikasikan.</p>
        </div>

        <form action="{{ route('home') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
            <input type="search" name="search" value="{{ $search }}" placeholder="Cari artikel..."
                   class="form-input sm:w-64">
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search !== '')
                <a href="{{ route('home') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    @if ($search !== '')
        <p class="mb-4 text-sm text-gray-600">
            Ditemukan {{ $articles->total() }} artikel untuk "<span class="font-medium">{{ $search }}</span>".
        </p>
    @endif

    @if ($articles->isEmpty())
        <div class="card px-4 py-12 text-center text-sm text-gray-500">Belum ada artikel yang dapat ditampilkan.</div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <article class="card flex flex-col overflow-hidden transition hover:shadow-md">
                    <a href="{{ route('public.articles.show', $article) }}">
                        @if ($article->image_url)
                            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="h-44 w-full object-cover">
                        @else
                            <div class="flex h-44 w-full items-center justify-center bg-gradient-to-br from-indigo-50 to-indigo-100 text-sm font-medium text-indigo-400">
                                Tanpa Gambar
                            </div>
                        @endif
                    </a>

                    <div class="flex flex-1 flex-col p-5">
                        <h2 class="text-lg leading-snug font-semibold text-gray-900">
                            <a href="{{ route('public.articles.show', $article) }}" class="hover:text-indigo-600">{{ $article->title }}</a>
                        </h2>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $article->user?->name ?? 'Anonim' }} · {{ $article->created_at->format('d M Y') }}
                        </p>
                        <p class="mt-3 flex-1 text-sm text-gray-600">{{ $article->excerpt }}</p>
                        <a href="{{ route('public.articles.show', $article) }}" class="link mt-4 text-sm">Baca selengkapnya →</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Memakai view pagination Tailwind bawaan Laravel. --}}
        <div class="mt-8">{{ $articles->links() }}</div>
    @endif
@endsection
