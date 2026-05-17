@if($d->status == 'in_stock')
    <span class="badge bg-success">In Stock</span>
@elseif($d->status == 'sold')
    <span class="badge bg-danger">Sold</span>
@else
    <span class="badge bg-secondary">{{ ucfirst($d->status) }}</span>
@endif
