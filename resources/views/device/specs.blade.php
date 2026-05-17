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
    @if ($d->condition_type)
        <span class="badge bg-info">{{ ucfirst($d->condition_type) }}</span>
    @endif
    @if ($d->battery_health)
        <span class="badge bg-warning text-dark">BH: {{ $d->battery_health }}</span>
    @endif
    @if ($d->status)
        <span class="badge {{ $d->status == 'in_stock' ? 'bg-success' : ($d->status == 'sold' ? 'bg-danger' : 'bg-warning') }}">
            {{ ucfirst(str_replace('_', ' ', $d->status)) }}
        </span>
    @endif
</small>
