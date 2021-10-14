
@extends('admin.admin_master')
@section('admin')

<div class="container-full">
<!-- Main content -->
<section class="content">
<div class="box">
			<div class="box-header with-border">
			  <h4 class="box-title">Admin Change password</h4>
			</div>
			<!-- /.box-header -->
			<div class="box-body">
			  <div class="row">
				<div class="col">
					<form action="{{route('update.change.password')}}" method="Post">
					@csrf  
					<div class="row">
						<div class="col-12">						
		<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<h5>Current Password <span class="text-danger">*</span></h5>
						<div class="controls">
							<input type="password" id="current_password" name="oldpassword" value="" class="form-control" required=""> 
						</div>
					</div>

                    <div class="form-group">
						<h5>New Password <span class="text-danger">*</span></h5>
						<div class="controls">
							<input type="password" id="password" name="password" value="" class="form-control" required=""> 
						</div>
					</div>

                    <div class="form-group">
						<h5>Confirm Password <span class="text-danger">*</span></h5>
						<div class="controls">
							<input type="password" id="password_confirmation" name="password_confirmation" value="" class="form-control" required=""> 
						</div>
					</div>
				</div>  <!--end col md 6 -->
				
        </div>
					
						<div class="text-xs-right">
							<button type="submit" class="btn btn-rounded btn-info">Update</button>
						</div>
					</form>

				</div>
				<!-- /.col -->
			  </div>
			  <!-- /.row -->
			</div>
			<!-- /.box-body -->
            </div>  
		  </div>    



@endsection