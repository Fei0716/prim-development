@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('assets/css/required-asterick.css')}}" rel="stylesheet">
{{-- <link href="{{ URL::asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.css') }}" rel="stylesheet"> --}}
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="{{ URL::asset('assets/css/datatable.css')}}">
@include('layouts.datatable')
@endsection

@section('content')
<div class="row align-items-center">
    <div class="col-sm-6">
        <div class="page-title-box">
            <h4 class="font-size-18">Relief Management</h4>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">

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

                <div class="form-group">
                    <label>Tarikh</label>
                    <input type="text" value="" class="form-control" name="pickup_date" id="datepicker"  placeholder="Pilih tarikh" readonly required>
                </div>

                <div class="form-group">
                    <label>Auto Suggestion Sort By:</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="0" selected disabled>Sort By</option>
                        <option value="Beban Guru">Beban Guru</option>
                        <option value="Kelas">Kelas</option>
                    </select>
                </div>

               

                <!-- <a style="margin: 19px; float: right;" onclick="autoSuggest()" class="btn btn-primary"> <i class="fas fa-plus"></i> Auto Suggestion</a> -->
            </div>

            {{-- <div class="">
                <button onclick="filter()" style="float: right" type="submit" class="btn btn-primary"><i
                        class="fa fa-search"></i>
                    Tapis</button>
            </div> --}}

        </div>
    </div>

    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

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
                @if(\Session::has('error'))
                <div class="alert alert-danger">
                    <p>{{ \Session::get('error') }}</p>
                </div>
                @endif

                <div class="flash-message"></div>

                <div class="table-responsive">
                    <table id="reliefTable" class="table table-bordered table-striped dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr style="text-align:center">
                                <th>No </th>
                                <th>Kelas</th>
                                <th>Subjek</th>
                                <th>Slot</th>
                                <th>Guru Asal</th>
                                <th>Alasan</th>
                                <th>Guru Ganti</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <a style="margin: 19px;" href="#" class="btn btn-primary ml-auto" data-toggle="modal" data-target="#modelId">
                        <i class="fas fa-plus"></i> Add Row
                    </a>
                </div>

               
                <div class="row">
                    <!-- <a style="margin: 10px;" class="btn btn-danger">
                        <i class="fas fa-plus"></i> Discard
                    </a> -->

                    <form action="{{route('schedule.saveRelief')}}" method="post" id="commitReliefForm">
                        @csrf 
                        <input type="hidden" name="commitRelief" id="commitReliefInput">
                        <input type="hidden" name="organization" id="commitReliefOrg">
                        <a style="margin: 10px;" class="btn btn-success" onclick="saveRelief()">
                            <i class="fas fa-plus"></i> Confirm and Notify Related Teachers
                        </a>
                    </form>
                   
                </div>

            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modelId" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Row</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{route('schedule.addTeacherLeave')}}" method="post" enctype="multipart/form-data">
                        <div class="modal-body">

                            {{ csrf_field() }}

                            
                            <div class="form-group">

                            <div class="form-group">
                            <label>Teacher Name</label>
                            <select name="selectedTeacher" id="selectedTeacher" class="form-control">
                              
                            </select>
                            </div>
                            <div class="form-group">
                                <label>Time of Leave</label>
                                <input type="radio" name="isLeaveFullDay" id="fullday" onclick="displaySelectTimeFull()" checked> Full Day
                                <input type="radio" name="isLeaveFullDay2" id="period" onclick="displaySelectTime()"> Period
                            </div>
                            
                            <div id="selectTime" style="display: none;">
                                <div class="form-group">
                                    <label>Start Time</label>
                                    <input type="time" name="starttime" id="starttime" class="form-control">
                                </div>
                                <div class="form-group" >
                                    <label>End Time</label>
                                    <input type="time" name="endtime" id="endtime" class="form-control">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <select name="reason" id="reason" class="form-control">
                                
                                </select>
                            </div>
                            <input type="hidden" name="date" value="" id="rowdate">
                            <div class="form-group">
                                <label for="note">Note</label>
                                <input type="text" name="note" id="note" placeholder="Enter your note here..." class="form-control">
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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

<script>   
    var dates = []
    $(document).ready(function() {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#datepicker").datepicker("setDate", new Date());
        dateOnChange();

        

        $('#organization').change(function() {
            var organizationid = $("#organization option:selected").val();
            $('#reliefTable').DataTable().destroy();

            $.ajax({
                url: "{{ route('schedule.getTeacherOfOrg') }}",
                type: 'POST',
                data: {
                    organization: $('#organization option:selected').val(),
                },
                success: function (response) {
                    response.teachers.forEach(function(teacher){
                        $('#selectedTeacher').append('<option value="' + teacher.teacher_id + '">' + teacher.name + '</option>');
                    });

                    response.leaveType.forEach(function(type){
                        $('#reason').append('<option value="' + type.id + '">' + type.type + '</option>');
                    });
                }
            });


            // console.log(organizationid);
            // fetch_data(organizationid);
        });

        if ($("#organization").val() != "") {
            $("#organization").prop("selectedIndex", 1).trigger('change');
            // fetch_data($("#organization").val());
        }

        $('#sort').on('change', function () {
            autoSuggest();
        });

        // csrf token for ajax
       

        $('.alert').delay(3000).fadeOut();

        $('#datepicker').change(function() {
            dateOnChange();
           fetchReliefData();
        })
        
        

        // Function to display relief data in the table
       
        // var table = $('#reliefTable');
        // console.log(table);

        // Initial fetch when the page loads
        fetchReliefData();

        });

        $("#datepicker").datepicker({
            minDate: 0,
            maxDate: '+1m',
            dateFormat: 'yy-mm-dd',
            dayNamesMin: ['Ahd', 'Isn', 'Sel', 'Rab', 'Kha', 'Jum', 'Sab'],
            beforeShowDay: editDays,
            defaultDate: 0, 
        })

       

        function assignTeacher(leave_relief_id) {
           // console.log('Selected teacher for row ' + (schedule_subject_id) + ': ' + teacher);

            // Make an AJAX request to get available teachers for the selected slot
            $.ajax({
                url: "{{ route('schedule.getFreeTeacher') }}",
                type: 'POST',
                data: {
                    organization: $('#organization option:selected').val(),
                    date: $('#datepicker').val(),
                    leave_relief_id: leave_relief_id
                },
                success: function (response) {
                    console.log(response);

                    // Update the combo box that selects the teacher
                    updateTeacherComboBox(leave_relief_id, response.free_teacher_list);
                    
                    // console.log(response.free_teacher_list);
                    // Additional logic if needed
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });

            // Add your logic to handle the selected teacher here
            // You can make an AJAX request to update the server or perform other actions
        }


        function saveRelief(){
            var tableBody = $('#reliefTable tbody');
            var assignTeacherElements = tableBody.find('.assign_teacher');

            let commitRelief = []; 
            assignTeacherElements.each(function(index, element) {
                        var leave_relief = $(this).attr('data-index');
                        // Use direct property access instead of split
                        var teacher = $(this).val();
                        if(teacher == 0 || teacher ==null) 
                            return true;
                            commitRelief.push(leave_relief+'-'+teacher);
                    });

            if(commitRelief.length ==0)
            {
                alert('No update be make');
                return;
            }
            console.log(commitRelief);
            let commitReliefOrg = $('#organization option:selected').val();
            $('#commitReliefInput').val(JSON.stringify(commitRelief));
            $('#commitReliefOrg').val(commitReliefOrg);
            $('#commitReliefForm').submit();
        }


        function displayRelief(reliefData) {
        console.log('Relief Data:', reliefData);

                var tableBody = $('#reliefTable tbody');
                tableBody.empty();

                reliefData.forEach(function (relief, index) {
                    var row = $('<tr></tr>');
                    row.append('<td>' + (index + 1) + '</td>');
                    row.append('<td>' + relief.class_name + '</td>');
                    row.append('<td>' + relief.subject + '</td>');
                    row.append('<td>' + relief.slot + '</td>');
                    row.append('<td>' + relief.leave_teacher + '</td>');
                    if (relief.desc !== null) {
                    row.append('<td>' + relief.desc + '</td>');
                }else{
                    row.append('<td></td>');
                }

        // Append the select box with options
        var selectColumn = $('<td><select class="form-control assign_teacher" data-index="' + relief.leave_relief_id  + '" schedule_subject_id="'+relief.schedule_subject_id+'"></select></td>');

        var selectElement = selectColumn.find('select');

        // Call the updated function to populate the select box options
       //git  updateTeacherComboBox(index, response.original.free_teacher_list);

        row.append(selectColumn);
        tableBody.append(row);
    });

            var assignTeacherElements = tableBody.find('.assign_teacher');

// Loop through each found element
assignTeacherElements.each(function(index, element) {
            var selectedIndex = $(this).attr('data-index');
            // Use direct property access instead of split
            var leave_relief_id = selectedIndex;
            assignTeacher(leave_relief_id);
    
    // Your code to handle each element goes here console.log($(element).text()); // Example: Log the text content of each element using jQuery
});
autoSuggest();

    // tableBody.on('mousedown', '.assign_teacher', function () {
    //     var selectedIndex = $(this).data('index');
    // // Use direct property access instead of split
    // var schedule_subject_id = selectedIndex;
            // var selectedTeacher = $(this).val();
            // assignTeacher(schedule_subject_id, selectedTeacher);
            // });
        }

// Call fetchReliefData with the corrected success function
function fetchReliefData() {
    let date_val = $('#datepicker').val();

    if (!date_val) {
        date_val = $.datepicker.formatDate('yy-mm-dd', new Date());
        $('#datepicker').datepicker('setDate', date_val);
    }

    $.ajax({
        url: '{{ route("schedule.getPendingRelief") }}',
        type: 'POST',
        data: {
            organization: $('#organization option:selected').val(),
            date: date_val,
        },
        success: function (response) {
            console.log(response);
            displayRelief(response.pending_relief);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}


function updateTeacherComboBox(leave_relief_id,availableTeachers) {

    // Find the select box associated with the given schedule_subject_id
    var selectBox = $('.assign_teacher[data-index="' + leave_relief_id + '"]');

    // Clear existing options
    selectBox.empty();

    //Check if availableTeachers is defined
    selectBox.append('<option selected disabled >No Teacher</option>');
    if (availableTeachers) {
        // If availableTeachers is an object, extract the array
        var teachersArray = Array.isArray(availableTeachers) ? availableTeachers : availableTeachers.free_teacher_list;

        // Check if the extracted array is not empty
        if (Array.isArray(teachersArray) && teachersArray.length > 0) {
            // Iterate over the array and add options
            teachersArray.forEach(function (teacher) {
                selectBox.append('<option value="' + teacher.id + '">' + teacher.name + '</option>');
            });
        }
    }
    
}

    function autoSuggest(){
        var organization = $("#organization option:selected").val();
        var tableBody = $('#reliefTable tbody');
        var assignTeacherCombobox = tableBody.find('.assign_teacher');
        let pendingRelief = []; 
        assignTeacherCombobox.each(function (index, relief){
        // There's a typo in 'schedule_subvject_id'. It should be 'schedule_subject_id'
            var ss_id = $(relief).attr('schedule_subject_id');
            var lr_id = $(relief).attr('data-index');

            // There's a typo in 'ss.id'. It should be 'ss_id'
            pendingRelief.push(lr_id+'-'+ss_id);
        });
        console.log(pendingRelief)//get from each row and format it, 'leave_relief_id-schedule_subject_id'
        var criteria =  $('#sort').val();
       
        $.ajax({
                url: "{{route('schedule.autoSuggestRelief')}}",
                type: 'POST',
                data: {
                    organization: organization, 
                    pendingRelief: pendingRelief, 
                    criteria: criteria,
                    date: $('#datepicker').val(),
                },
                success: function(response) {
                    console.log(response);
                     response.relief_draft.forEach(function(draft,index){
                        //var combobox = assignTeacherCombobox.has("[data-index='"+draft.leave_relief_id+"']");
                        var combobox = $('.assign_teacher[data-index="'+draft.leave_relief_id+'"]');
                        //combobox.val('17478');
                        combobox.val(draft.teacher_id)
                        console.log(draft.teacher_id)
                     })
                },
                error: function(xhr, status, error) {
                    console.log(error);
                }
            });
        }

        function dateOnChange() {
            let date_val = $('#datepicker').val(), timePicker = $('#timepicker'), timeRange = $('.time-range')
            let org_id = $('#organization option:selected').val()
            
             console.log(date_val)
            $('#rowdate').val(date_val);
            if(date_val != '') {
                $('.pickup-time-div').removeAttr('hidden')
               
            } else {
                $('.pickup-time-div').attr('hidden', true)
            }
        }

    var disabledDates = dates
    
    function editDays(date) {
      for (var i = 0; i < disabledDates.length; i++) {
        if (new Date(disabledDates[i]).toString() == date.toString()) {             
          return [false];
        }
      }
      return [true];
    }

    function displaySelectTime(){
        var selectTimeDiv = document.getElementById('selectTime');
        var periodRadio = document.getElementById('period');
        var fulldayRadio = document.getElementById('fullday');
        
        if (periodRadio.checked) {
            selectTimeDiv.style.display = 'block';
            fulldayRadio.checked = false;
        } else {
            selectTimeDiv.style.display = 'none';
        }

    }

    function displaySelectTimeFull(){
        var selectTimeDiv = document.getElementById('selectTime');
        var periodRadio = document.getElementById('period');
        var fulldayRadio = document.getElementById('fullday');

        if (fulldayRadio.checked) {
            selectTimeDiv.style.display = 'none';
            periodRadio.checked = false;
        } else {
            selectTimeDiv.style.display = 'block';
        }
    }
</script>
@endsection