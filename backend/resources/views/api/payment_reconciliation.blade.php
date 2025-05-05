<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<div class="page-content">

    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">RECONCILIATION</h6>
                <form class="forms-sample" method="POST" id="nmb_reconciliation_form" enctype="multipart/form-data"
                    action="">
                    @csrf
                    <div class="row col-sm-12 col-12 col-md-12">

                        <div class="col-md-8">
                            <div class="form-group row">
                                <label for="reconcile_date" class="col-sm-3 col-form-label">Reconciliation Date</label>
                                <div class="col-sm-9">
                                    <input type="date" name="reconcile_date" class="form-control" />
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
    <div class="col-sm-12 col-12 col-md-12 grid-margin stretch-card" id="nmb_reconciliation_form_response">
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {

    $('#nmb_reconciliation_form').submit(function(event) {
        event.preventDefault(); // Prevent the default form submission

        var formData = $(this).serialize(); // Serialize the form data

        $.ajax({
            url: "{{route('nmb_reconciliation')}}",
            type: 'POST', // or 'GET', 'PUT', 'DELETE', etc.
            data: formData, // Pass the serialized form data
            beforeSend: function() {
                $('#nmb_reconciliation_form_response').html(
                    '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>'
                );
                // Show loading indicators or perform other setup tasks here
            },
            success: function(response) {
                $('#nmb_reconciliation_form_response').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#nmb_reconciliation_form_response').html(
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