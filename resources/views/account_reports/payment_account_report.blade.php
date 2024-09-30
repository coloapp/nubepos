@extends('layouts.app')
@section('title', __('account.payment_account_report'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">{{ __('account.payment_account_report') }}</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        <div class="row">
            <div class="col-md-12">
                @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('account_id', __('account.account') . ':') !!}
                        {!! Form::select('account_id', $accounts, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'id' => 'date_filter',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="payment_account_report">
                            <thead>
                                <tr>
                                    <th>@lang('messages.date')</th>
                                    <th>@lang('account.payment_ref_no')</th>
                                    <th>@lang('account.invoice_ref_no')</th>
                                    <th>@lang('sale.amount')</th>
                                    <th>@lang('lang_v1.payment_type')</th>
                                    <th>@lang('account.account')</th>
                                    <th>@lang('lang_v1.description')</th>
                                    <th>@lang('messages.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')

    <script type="text/javascript">
        $(document).ready(function() {

            if ($('#date_filter').length == 1) {

                $('#date_filter').daterangepicker(
                    dateRangeSettings,
                    function(start, end) {
                        $('#date_filter').val(start.format(moment_date_format) + ' ~ ' + end.format(
                            moment_date_format));
                        payment_account_report.ajax.reload();
                    }
                );

                $('#date_filter').on('cancel.daterangepicker', function(ev, picker) {
                    $(this).val('');
                    payment_account_report.ajax.reload();
                });
            }

            payment_account_report = $('#payment_account_report').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader:false,
                "ajax": {
                    "url": "{{ action([\App\Http\Controllers\AccountReportsController::class, 'paymentAccountReport']) }}",
                    "data": function(d) {
                        d.account_id = $('#account_id').val();
                        var start_date = '';
                        var endDate = '';
                        if ($('#date_filter').val()) {
                            var start_date = $('#date_filter').data('daterangepicker').startDate.format(
                                'YYYY-MM-DD');
                            var endDate = $('#date_filter').data('daterangepicker').endDate.format(
                                'YYYY-MM-DD');
                        }
                        d.start_date = start_date;
                        d.end_date = endDate;
                    }
                },
                columnDefs: [{
                    "targets": 7,
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'paid_on',
                        name: 'paid_on'
                    },
                    {
                        data: 'payment_ref_no',
                        name: 'payment_ref_no'
                    },
                    {
                        data: 'transaction_number',
                        name: 'transaction_number'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'type',
                        name: 'T.type'
                    },
                    {
                        data: 'account',
                        name: 'account'
                    },
                    {
                        data: 'details',
                        name: 'details',
                        "searchable": false
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#payment_account_report'));
                }
            });

            $('select#account_id, #date_filter').change(function() {
                payment_account_report.ajax.reload();
            });
        })

        $(document).on('submit', 'form#link_account_form', function(e) {
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: $(this).attr("method"),
                url: $(this).attr("action"),
                dataType: "json",
                data: data,
                success: function(result) {
                    if (result.success === true) {
                        $('div.view_modal').modal('hide');
                        toastr.success(result.msg);
                        payment_account_report.ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                }
            });
        });
    </script>
@endsection
