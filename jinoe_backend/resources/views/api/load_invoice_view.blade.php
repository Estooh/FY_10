
<div class="card">
    <div class="card-body">
        <h6 class="card-title">Report By Payment Type</h6>        
        <div class="table-responsive">
            <table id="dataTableExample" class="table">
                <thead>
                    <?php
//                    print_r($invoices);
                    ?>
                    <tr>
                        <th>SN</th>
                        <th>Customer Name</th>
                        <th>Payment Reference No</th>
                        <th>Payment Type</th>
                        <th>Invoiced Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sn = 0;
                    foreach ($invoices as $invoice) {
                        $sn++;
                        ?>
                        <tr>
                            <td><?= $sn ?></td>
                            <td><?= $invoice->customer_name ?></td>
                            <td><?= $invoice->payment_ref_no ?></td>
                            <td><?= $invoice->payment_type_name ?></td>
                            <td><?= $invoice->inv_amount ?></td>
                            <td><?= $invoice->total_payed ?></td>
                            <td><?= $invoice->balance ?></td>
                            <td><button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target=".bd-example-modal-xl">View</button></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title h4" id="myExtraLargeModalLabel">Extra large modal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                ...
            </div>
        </div>
    </div>
</div>
<script>
    function loadView(url){
        
    }
    $(function () {
        'use strict';

        $(function () {
            $('#dataTableExample').DataTable({
                "aLengthMenu": [
                    [10, 30, 50, -1],
                    [10, 30, 50, "All"]
                ],
                "iDisplayLength": 10,
                "language": {
                    search: ""
                }
            });
            $('#dataTableExample').each(function () {
                var datatable = $(this);
                // SEARCH - Add the placeholder for Search and Turn this into in-line form control
                var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
                search_input.attr('placeholder', 'Search');
                search_input.removeClass('form-control-sm');
                // LENGTH - Inline-Form control
                var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
                length_sel.removeClass('form-control-sm');
            });
        });

    });
</script>

