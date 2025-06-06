@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('assets/libs/chartist/chartist.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ URL::asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="{{ URL::asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@include('layouts.datatable')

@endsection

@section('content')
<div class="row align-items-center">
    <div class="col-sm-6">
        <div class="page-title-box">
            <h4 class="font-size-18">Carian Laporan</h4>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            {{-- <div class="card-header">List Of Applications</div> --}}
            {{-- <div>
                <a style="margin: 19px; float: right;" id="btn-download" class="btn btn-primary"> <i
                        class="fas fa-download"></i> Muat Turun PDF</a>
            </div> --}}
            <form method="POST" action="{{ route('fees.generateExcelClassTransaction') }}">
            @csrf
            <div class="card-body">
                {{csrf_field()}}
                <div class="card-body">

                    <div class="form-group">
                        <label>Nama Organisasi</label>
                        <select name="organization" id="organization" class="form-control">
                            <option value="" selected disabled>Pilih Organisasi</option>
                            @foreach($organization as $row)
                            <option value="{{ $row->id }}">{{ $row->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="dkelas" class="form-group">
                        <label> Kelas</label>
                        <select name="classes" id="classes" class="form-control">
                            <option value="" disabled selected>Pilih Kelas</option>

                        </select>
                    </div>

                    <div class="form-row">
                    <div class="form-group col-md-12 required">
                        <label class="control-label">Tempoh Transaksi</label>

                        <div class="input-daterange input-group" id="date">
                            <input type="text" class="form-control" id="date_started" name="date_started" placeholder="Tarikh Awal"
                                autocomplete="off" data-parsley-required-message="Sila masukkan tarikh awal"
                                data-parsley-errors-container=".errorMessage" required />
                            <input type="text" class="form-control"  id="date_end" name="date_end" placeholder="Tarikh Akhir"
                                autocomplete="off" data-parsley-required-message="Sila masukkan tarikh akhir"
                                data-parsley-errors-container=".errorMessage" required />
                        </div>
                        <div class="errorMessage"></div>
                    </div>
                    <div class="form-group col-md-12 required">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="show_all_payments" name="show_all_payments" checked>
                            <label class="form-check-label" for="show_all_payments">
                                Tunjuk semua bayaran pelajar
                            </label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-12 required">
                        
                            <button class= "btn btn-primary" type="submit" id="excel_btn">Download In Excel</button>

                        </div>
                    </div>
                </div>
                @if(count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                        <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if(\Session::has('success'))
                <div class="alert alert-success">
                    <p>{{ \Session::get('success') }}</p>
                </div>
                @endif

                <div class="table-responsive">
                    <table id="studentTable" class="table table-bordered table-striped dt-responsive nowrap"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr style="text-align:center">
                                <th> No. </th>
                                <th>Nama Penuh</th>
                                <th>Jantina</th>
                                <th>Status Yuran</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            </form>
        </div>

    </div>
</div>
@endsection


@section('script')
<!-- Peity chart-->
<script src="{{ URL::asset('assets/libs/peity/peity.min.js')}}"></script>

<!-- Plugin Js-->
<script src="{{ URL::asset('assets/libs/chartist/chartist.min.js')}}"></script>

<script src="{{ URL::asset('assets/js/pages/dashboard.init.js')}}"></script>

<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" defer></script>

<script src="{{ URL::asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }} "></script>

<!-- Buttons examples -->
<script src="{{ URL::asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/jszip/jszip.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/pdfmake/pdfmake.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/pdfmake/vfs_fonts.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}" defer></script>

<!-- Responsive examples -->
<script src="{{ URL::asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }} "></script>
<script src="{{ URL::asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }} "></script>

<script>
    $(document).ready(function(){
        
        var studentTable;
        $('#date').datepicker({
                toggleActive: true,
                todayHighlight:true,
                startDate: new Date("2010-01-01"),
                format: 'yyyy-mm-dd',
                autoclose: true,
                orientation: 'bottom',
                defaultdate : new Date()
               
            });

            $('#date_started').val(new Date().toISOString().split('T')[0]);
            $('#date_end').val(new Date().toISOString().split('T')[0]);

            

        if($("#organization").val() != ""){
            $("#organization").prop("selectedIndex", 1).trigger('change');
            fetchClass($("#organization").val());
        }

        
        $('#excel_btn').on('click', function() {
            studentTable.button('.buttons-excel').trigger();
        });

        // fetch_data();
        // alert($("#organization").val());

            function fetch_data(cid = '') {
                //console.log($("#organization").val());
                studentTable = $('#studentTable').DataTable({
                    // dom: 'Bfrtip',
                    // buttons: [
                    //     {
                    //         extend: 'excelHtml5',
                    //         action: function (e, dt, button, config) {
                    //             // store current pagination
                    //             let oldStart = dt.settings()[0]._iDisplayStart;

                    //             // Fetch all data from server
                    //             dt.one('preXhr', function (e, s, data) {
                    //                 data.start = 0;
                    //                 data.length = 2147483647; // huge number = all rows

                    //                 dt.one('preDraw', function (e, settings) {
                    //                     // Call original button action
                    //                     $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);

                    //                     // Restore paging
                    //                     dt.one('preXhr', function (e, s, data) {
                    //                         data.start = oldStart;
                    //                     });

                    //                     // Reload data
                    //                     setTimeout(dt.ajax.reload, 0);

                    //                     return false;
                    //                 });
                    //             });

                    //             // Trigger AJAX reload
                    //             dt.ajax.reload();
                    //         },       
                    //         title: 'Laporan Yuran Pelajar',
                    //         text: 'Export to Excel',
                    //         className: 'd-none', // hide the actual DataTable button
                    //         exportOptions: {
                    //             columns: ':visible'
                    //         }
                    //     }
                    // ],
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('fees.getStudentDatatableFees') }}",
                            data: {
                                classid: cid,
                                orgId : $("#organization").val(),
                                hasOrganization: true,
                                start_date:$('#date_started').val(),
                                end_date: $('#date_end').val(),
                                show_all_payments: $('#show_all_payments').is(":checked")
                            },
                            type: 'GET',

                        },
                        'columnDefs': [{
                            "targets": [0], // your case first column
                            "className": "text-center",
                            "width": "2%"
                        },{
                            "targets": [2,3], // your case first column
                            "className": "text-center",
                        },],
                        order: [
                            [1, 'asc']
                        ],
                        columns: [{
                            "data": null,
                            searchable: false,
                            "sortable": false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        }, {
                            data: "nama",
                            name: 'nama'
                        }, {
                            data: "gender",
                            name: 'gender'
                        }, {
                            data: 'status',
                            name: 'status',
                            orderable: false,
                            searchable: false
                        },]
                });
            }

            $('#organization').change(function() {
               
                var organizationid    = $("#organization").val();
                var _token            = $('input[name="_token"]').val();

                fetchClass(organizationid);
                
            });

            function fetchClass(organizationid = ''){
                var _token            = $('input[name="_token"]').val();
                $.ajax({
                    url:"{{ route('student.fetchClass') }}",
                    method:"POST",
                    data:{ oid:organizationid,
                            _token:_token },
                    success:function(result)
                    {
                        $('#classes').empty();
                        $("#classes").append("<option value='' disabled selected> Pilih Kelas</option>");
                        jQuery.each(result.success, function(key, value){
                            // $('select[name="kelas"]').append('<option value="'+ key +'">'+value+'</option>');
                            $("#classes").append("<option value='"+ value.cid +"'>" + value.cname + "</option>");
                        });
                    }
                })
            }

            function downloadPDF(cid = ''){
                $.ajax({
                    url:"{{ route('fees.generatePDFByClass') }}",
                    method:"GET",
                    data:{ 
                        class_id:cid,
                    },
                    success:function(result)
                    {
                        
                    }
                })
            }

          

            $('#classes').change(function() {
                var organizationid    = $("#organization option:selected").val();

                var classid    = $("#classes option:selected").val();
                if(classid){
                    $('#studentTable').DataTable().destroy();
                    fetch_data( classid);
                    
                }
                // console.log(organizationid);
            });

            $('#date_started, #date_end, #show_all_payments').on('change', function() {
              // Call validateDateRange function when either datepicker changes
              $('#classes').trigger('change');
          });

            // csrf token for ajax
            $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

                $('.alert').delay(3000).fadeOut();

          

        });


//         function downloadExcel(){

// if ($("#organization").val() === null || $("#organization").val() === "") {
//     alert('Organization Not Selected!');
//     return;
// }

// $.ajax({
//     url:"{{ route('fees.generateExcelClassTransaction') }}",
//     method:"GET",
//     data:{ 
//         class_id: $("#classes option:selected").val(),
//         orgId : $("#organization").val(),
//         start_date:$('#date_started').val(),
//         end_date: $('#date_end').val()
//     },
//     success:function(result)
//     {
        
//     }
// })
// }
        
        
</script>

@endsection