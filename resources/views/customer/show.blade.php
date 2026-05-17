@extends('layouts.app')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4">
				<div class="card">
					<div class="card-header">Customer Details</div>
					<div class="card-body">
						<h5>{{ $customer->name }}</h5>
						<p><strong>Phone:</strong> {{ $customer->phone }}</p>
						<p><strong>Email:</strong> {{ $customer->email }}</p>
						<p><strong>Address:</strong> {{ $customer->address }}</p>
						<a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning btn-sm">Edit</a>
						<a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">Back</a>
					</div>
				</div>
			</div>
			<div class="col-md-8">
				<div class="card">
					<div class="card-header">Invoices</div>
					<div class="card-body">
						@if($customer->invoices->isEmpty())
							<p>No invoices found for this customer.</p>
						@else
							<div class="table-responsive">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>Invoice No</th>
											<th>Items</th>
											<th>Date</th>
											<th>Total</th>
											<th>Paid</th>
											<th>Action</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($customer->invoices as $invoice)
											@php
												$item = $invoice->items->first();
											@endphp

											<tr>
												<td>{{ $invoice->invoice_no ?? '-' }}</td>

												<td>
													@if($item?->item_type === 'device')
														{{ $item?->item?->model?->name ?? 'N/A' }}
														(
														{{ $item?->item?->storage ?? '-' }} -
														{{ $item?->item?->ram ?? '-' }}
														)
														<br>
														<small>
															IMEI: {{ $item->deviceImei->imei ?? 'N/A' }}
														</small>

													@elseif($item)
														{{ $item?->item?->name ?? 'N/A' }}
														<br>
														<small>
															Serial No: {{ $item?->item?->sku ?? 'N/A' }}
														</small>

													@else
														<span class="text-muted">No Item</span>
													@endif
												</td>

												<td>{{ $invoice->invoice_date ?? '-' }}</td>
												<td>{{ number_format($invoice->total_amount ?? 0, 2) }}</td>
												<td>{{ number_format($invoice->paid_amount ?? 0, 2) }}</td>

												<td>
													<a href="{{ route('invoice.show', $invoice->id) }}" class="btn btn-info btn-sm">
														View
													</a>
												</td>
											</tr>
										@endforeach
									</tbody>

								</table>
							</div>
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection