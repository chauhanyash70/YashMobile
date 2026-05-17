<small>
    @if ($d->storage)
        <span class="badge bg-dark">{{ $d->storage }}</span>
    @endif
    @if ($d->ram)
        <span class="badge bg-primary">{{ $d->ram }}</span>
    @endif
    @if ($d->color)
        <span class="badge bg-secondary">{{ $d->color }}</span>
    @endif
    @if ($d->condition)
        <span class="badge bg-info">{{ ucfirst($d->condition) }}</span>
    @endif
</small>
