@extends('admin.admin_dashboard')
@section('admin')
<!-- Script -->
<div class="page-content">

    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">VIEW INVOICE</h6>
                <form class="forms-sample" method="POST" id="nmb_check_invoice_form" enctype="multipart/form-data">
                    @csrf
                    <div class="row col-sm-12 col-12 col-md-12">

                        <div class="col-md-5">
                            <div class="form-group row">
                                <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                <div class="col-sm-9">
                                    <select class="form-control" id="customer_id" name="customer_id">
                                        <option selected value="">--Any--</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group row">
                                <button type="submit" class="btn btn-primary mr-2">Search</button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
    <div class="col-sm-12 col-12 col-md-12 grid-margin stretch-card" id="nmb_check_invoice_response">
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {


    $('#nmb_check_invoice_form').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = $(this).serialize(); // Serialize the form data

        $.ajax({
            url: "{{route('nmb_check_invoice')}}",
            type: 'POST', // or 'GET', 'PUT', 'DELETE', etc.
            data: formData, // Pass the serialized form data
            beforeSend: function() {
                $('#nmb_check_invoice_response').html(
                    '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>'
                );
                // Show loading indicators or perform other setup tasks here
            },
            success: function(response) {
                $('#nmb_check_invoice_response').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error:', textStatus);
                console.log('Response Text:', jqXHR.responseText);
                $('#nmb_check_invoice_response').html(
                    '<div class="alert alert-danger" role="alert">' + jqXHR +
                    textStatus + '</div>');
            }
        });
    });




    $("#customer_id").select2({
        ajax: {
            url: "{{route('payment_customer_json')}}",
            type: "post",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    _token: "{{csrf_token()}}",
                    search: params.term // search term
                };
            },
            processResults: function(response) {
                return {
                    results: response
                };
            },
            cache: true
        }

    });

});
</script>
@endsection