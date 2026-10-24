<div class="order-controls {{ $class ?? '' }}" aria-label="Atur urutan {{ $subject ?? 'item' }}">
    @if($up)
        <form method="POST" action="{{ $up }}">
            @csrf
            <button type="submit" aria-label="Pindahkan {{ $subject ?? 'item' }} ke atas">⌃</button>
        </form>
    @endif
    @if($down)
        <form method="POST" action="{{ $down }}">
            @csrf
            <button type="submit" aria-label="Pindahkan {{ $subject ?? 'item' }} ke bawah">⌄</button>
        </form>
    @endif
</div>
