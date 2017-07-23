@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Your uploads</div>
                <div class="panel-body uploads">
                    @if (count($myFiles))
                        <ol>
                            @foreach ($myFiles as $f)
                                <li>
                                    <span class="filename">{{ $f['original_filename'] }}</span>

                                    <a href="/files/getoriginal/{{$f['id']}}" class="btn btn-primary">Original</a>
                                    @if (!empty($f['converted_filename']))
                                        <a href="/files/getpdf/{{$f['id']}}" class="btn btn-danger">PDF</a>
                                    @else
                                        <a href="#" class="btn btn-default" disabled>PDF</a>
                                    @endif
                                    <a href="/home/convertdialog/{{$f['id']}}" class="btn btn-success">Convert</a>
                                    <form action="/files/delete/{{$f['id']}}" method="POST" class="form-button delete">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="submit" class="btn btn-warning" value="Delete"/>
                                    </form>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        you upload no files yet
                    @endif
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading">New Upload</div>
                <div class="panel-body">
                        <form method="POST" action="/files/upload" class="form-convert" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" name="docs[]" accept="{{$extensions}}" multiple required>
                                <p class="help-block">{{$extensions}} formats</p>
                            </div>
                            <div class="form-group">
                                <input type="submit" name="submit" class="btn btn-success" value="Upload" />
                                <input type="submit" name="submit-convert" class="btn btn-danger" value="Upload and convert" />
                            </div>
                            {{ csrf_field() }}
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
