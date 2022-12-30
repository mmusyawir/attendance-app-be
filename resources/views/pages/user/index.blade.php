@extends('layouts.app')

@section('content')

<!-- Content Header (Page header) -->

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">User</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">User</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12">

                @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
                @endif

                <a href="{{ route('user.create') }}" class="btn btn-sm btn-primary mb-2">Add</a>
                <hr>
                <div class="form-group">
                    <h5>Filter User's Recap by Date Range</h5>

                    <label>Date Start</label>
                    <input type="date" name="range_start" class="form-control col-md-4 m-2">

                    <label>Date End</label>
                    <input type="date" name="range_end" class="form-control col-md-4 m-2">
                </div>
                <hr>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ion ion-clipboard mr-1"></i>
                            User
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">

                        <table class="table" id="datatable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>E-Mail</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
                <!-- /.card -->
            </section>
            <!-- /.Left col -->
        </div>
        <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
</section>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#datatable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: '{{ url("user") }}',
            columns: [
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ]
        });

        let startDate = null;
        let endDate = null;

        let recapUrl = '{{ route("user.recap", "dummy") }}';

        // on click class recap-button will trigger this function
        $(document).on('click', '.recap-button', function(event) {
            event.preventDefault();

            let id = $(this).attr('href');
            let url = recapUrl.replace('dummy', id);

            // if date range is not null
            if (startDate != null && endDate != null) {
                if (startDate > endDate) {
                alert('End date must be greater than start date');
                return;
            }

                url += '?start=' + startDate + '&end=' + endDate;
                // alert(url);

                // redirect to recap page in new tab

                window.open(url, '_blank');
            } else {
                alert('Please select date range first');
            }

        });

        $('input[name="range_start"]').on('change', function() {
            startDate = $(this).val();
        });

        $('input[name="range_end"]').on('change', function() {
            endDate = $(this).val();
        });
    });
</script>
@endpush
