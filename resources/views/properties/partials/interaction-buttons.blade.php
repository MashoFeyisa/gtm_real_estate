{{--
    Property Like & Favorite Buttons Partial

    Props passed from @include:
    - $property: Property model instance
    - $size: 'sm' | 'md' (default: 'sm')

    Usage:
      @include('properties.partials.interaction-buttons', ['property' => $property])
--}}
@php
    $isAuthenticated = auth()->check();
    $userId = auth()->id();
    $btnSize = $size ?? 'sm';

    $isLiked = $isAuthenticated
        ? \App\Models\PropertyLike::where('user_id', $userId)->where('property_id', $property->id)->exists()
        : false;

    $isFavorited = $isAuthenticated
        ? \App\Models\PropertyFavorite::where('user_id', $userId)->where('property_id', $property->id)->exists()
        : false;

    $likesCount = \App\Models\PropertyLike::where('property_id', $property->id)->count();
    $favoritesCount = \App\Models\PropertyFavorite::where('property_id', $property->id)->count();

    $btnClass = $btnSize === 'md'
        ? 'flex items-center gap-1.5 rounded-full px-3 py-2 text-xs font-bold transition-all duration-200 active:scale-90 cursor-pointer border'
        : 'flex items-center gap-1 rounded-full px-2.5 py-1.5 text-[11px] font-bold transition-all duration-200 active:scale-90 cursor-pointer border';

    $iconClass = $btnSize === 'md' ? 'h-4 w-4' : 'h-3.5 w-3.5';

    $likeUrl = route('properties.like', $property->id);
    $favoriteUrl = route('properties.favorite', $property->id);
    $registerUrl = route('register');
    $csrfToken = csrf_token();
@endphp

<div class="flex items-center gap-2 property-interaction-group"
     data-property-id="{{ $property->id }}"
     data-is-authenticated="{{ $isAuthenticated ? 'true' : 'false' }}"
     data-like-url="{{ $likeUrl }}"
     data-favorite-url="{{ $favoriteUrl }}"
    data-register-url="{{ $registerUrl }}"
     data-csrf="{{ $csrfToken }}"
     data-page-url="{{ url()->current() }}">

    {{-- Like Button --}}
    <button type="button"
            class="{{ $btnClass }} like-btn {{ $isLiked ? 'bg-rose-50 border-rose-300 text-rose-600' : 'bg-white/80 border-slate-200 text-slate-500 hover:bg-rose-50 hover:border-rose-300 hover:text-rose-600' }}"
            aria-label="Like this property"
            title="{{ ! $isAuthenticated ? 'Create an account to like this property' : ($isLiked ? 'Unlike' : 'Like').' this property' }}">
        <svg class="{{ $iconClass }} like-icon" fill="{{ $isLiked ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        <span class="like-count">{{ $likesCount > 0 ? $likesCount : '' }}</span>
    </button>

    {{-- Favorite / Bookmark Button --}}
    <button type="button"
            class="{{ $btnClass }} favorite-btn {{ $isFavorited ? 'bg-amber-50 border-amber-300 text-amber-600' : 'bg-white/80 border-slate-200 text-slate-500 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-600' }}"
            aria-label="Save to favorites"
            title="{{ ! $isAuthenticated ? 'Create an account to save this property' : ($isFavorited ? 'Remove from favorites' : 'Save to favorites') }}">
        <svg class="{{ $iconClass }} favorite-icon" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
        </svg>
        <span class="favorite-count">{{ $favoritesCount > 0 ? $favoritesCount : '' }}</span>
    </button>
</div>
