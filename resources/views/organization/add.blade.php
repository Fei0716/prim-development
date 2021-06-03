@extends('layouts.master')

@section('css')
<link href="{{ URL::asset('assets/libs/chartist/chartist.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="row align-items-center">
    <div class="col-sm-6">
        <div class="page-title-box">
            <h4 class="font-size-18">Organisasi</h4>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Organisasi >> Tambah Organisasi</li>
            </ol>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">

            @if(count($errors) > 0)
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            {{-- {{ route('sekolah.store') }} --}}
            <form method="post" action="{{ route('organization.store') }} " enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="card-body">
                    <div class="form-group">
                        <label>Nama Organisasi</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama Organisasi">
                    </div>
                    <div class="form-group">
                        <label>No Telefon</label>
                        <input type="text" name="telno" class="form-control" placeholder="No Telefon">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <label>Jenis Organisasi</label>
                        <select name="type_org" id="type_org" class="form-control">
                            <option value="" selected>Semua Jenis Organisasi</option>
                            @foreach($type_org as $row)
                            <option value="{{ $row->id }}">{{ $row->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cas Pembayaran (RM)</label>
                        <input id="input-currency" class="form-control input-mask text-left"
                            data-inputmask="'alias': 'numeric', 'groupSeparator': ',', 'digits': 2, 'digitsOptional': false, 'placeholder': '0'"
                            im-insert="true" style="text-align: right;" name="fixed_charges" required>
                    </div>
                    <div class="row justify-content-center">
                        <div class="form-group col-md-8">
                            <label>Alamat</label>
                            <textarea name="address" class="form-control" rows="4" placeholder="Alamat"></textarea>
                        </div>
                        <div class="form-group col">
                            <label>Poskod</label>
                            <input type="text" name="postcode" class="form-control" placeholder="Poskod">

                            <label>Negeri</label>
                            <input type="text" name="state" class="form-control" placeholder="Negeri">
                        </div>

                    </div>
                    <div class="form-group mb-0">
                        <div>
                            <button type="submit" class="btn btn-primary waves-effect waves-light mr-1">
                                Simpan
                            </button>
                        </div>
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

<script src="{{ URL::asset('assets/libs/inputmask/inputmask.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $(".input-mask").inputmask();
    });
</script>
@endsection