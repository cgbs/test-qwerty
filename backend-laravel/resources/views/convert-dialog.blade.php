@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Converter Options</div>
                    <div class="panel-body">
                        <form method="POST" class="form-convert" action="/files/convert-exists/{{$file->id}}">
                            <div class="form-group">
                                <h4>Convert {{$file->original_filename}} to PDF</h4>
                                <small>Uploaded: {{$file->updated_at}}</small>
                            </div>
                            <p class="help-block">addition options</p>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="convert-protect"> protect PDF
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="convert-rastr"> full rasterization (time-taking)
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-success btn-convert" value="Convert" />
                            </div>
                            {{ csrf_field() }}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection