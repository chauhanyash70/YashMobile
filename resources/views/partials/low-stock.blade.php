<table class="table table-sm table-hover">
    <thead class="table-light">
        <tr>
            <th>Name</th>
            <th class="text-center">Stock</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lowStockAccessories as $accessory)
            <tr>
                <td>
                    <div class="fw-bold">{{ $accessory->brand->name ?? '' }} {{ $accessory->name }}</div>
                    <small class="text-muted">{{ $accessory->hsn }}</small>
                </td>
                <td class="text-center">
                    <span class="fw-bold {{ $accessory->stock <= 2 ? 'text-danger' : 'text-warning' }}">
                        {{ $accessory->stock }}
                    </span>
                </td>
                <td>
                    @if($accessory->stock == 0)
                        <span class="badge bg-danger">Out of Stock</span>
                    @else
                        <span class="badge bg-warning text-dark">Low Stock</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center text-muted py-3">No low stock accessories</td>
            </tr>
        @endforelse
    </tbody>
</table>
