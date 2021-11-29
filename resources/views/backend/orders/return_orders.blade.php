@extends('admin.admin_master')
@section('admin')


  <!-- Content Wrapper. Contains page content -->
  
	  <div class="container-full">
		<!-- Content Header (Page header) -->
		 

		<!-- Main content -->
		<section class="content">
		  <div class="row">
			   
		 

			<div class="col-12">

			 <div class="box">
				<div class="box-header with-border">
				  <h3 class="box-title">Return Orders List</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
					<div class="table-responsive">
					  <table id="example1" class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Date </th>
								<th>Invoice </th>
								<th>Amount </th>
								<th>Payment </th>
                                <th>Reason</th>
								<th>Status </th>
								<th>Action</th>
								 
							</tr>
						</thead>
						<tbody>
	 @foreach($orders as $item)
	 <tr>
		<td> {{ $item->order_date }}  </td>
		<td> {{ $item->invoice_no }}  </td>
		<td> ${{ $item->amount }}  </td>
		<td> {{ $item->payment_method }}  </td>
        <td>{{ $item->return_reason }}</td>
		<td>
            @if($item->return_order == 1)
          
                   <span class="badge badge-pill badge-warning" style="background: #800000;"> Pending </span>
             

            @elseif($item->return_order == 2)
            <span class="badge badge-pill badge-warning" style="background: #008000;">Success </span>
            @endif    
        </td>
       
		<td width="25%">
		@if($item->return_order == 1)
			<a href="{{ route('return-pending-confirm',$item->id) }}">
					<span class="badge badge-pill badge-info"> Approve </span>
			</a>   
		@else
		<span class="badge badge-pill badge-warning" style="background: #008000;">Success </span>
		@endif
		</td>
							 
	 </tr>
	  @endforeach
						</tbody>
						 
					  </table>
					</div>
				</div>
				<!-- /.box-body -->
			  </div>
			  <!-- /.box -->

			          
			</div>
			<!-- /.col -->

 

 


		  </div>
		  <!-- /.row -->
		</section>
		<!-- /.content -->
	  
	  </div>
  



@endsection