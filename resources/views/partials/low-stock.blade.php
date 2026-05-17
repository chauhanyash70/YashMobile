<table class="table table-sm table-hover">
    <thead class="table-light">
        <tr>
            <th>Name</th>
            <th>Stock</th>
        </tr>
    </thead>
    <tbody>
        @forelse($lowStockAccessories as $acc)
            <tr>
                <td>{{ $acc->name }}</td>
                <td><span class="badge bg-danger">{{ $acc->stock }}</span></td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center text-muted">No low stock items</td>
            </tr>
        @endforelse
    </tbody>
</table>
