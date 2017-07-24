import React, { Component } from 'react';
import axios from 'axios';
import './PDFConverter.css';
import '../node_modules/bootstrap/dist/css/bootstrap.min.css';
import loader from './preloader.svg';

export class PDFConverter extends Component {
    constructor(props){
        super(props);
        this.state = {files:[]};
        this.acceptTypes = ['.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'];
        this.getFiles = this.getFiles.bind(this);
    }
    componentDidMount() {
        this.getFiles();
    }
    getFiles(){
        var self = this;
        axios.get(this.props.host+'/files').then(function (files) {
            files = files.data;
            for (var index = 0; index < files.length; index++) {
                files[index].actionDelete = self.props.host+'/deletefile/'+files[index].id;
                files[index].actionDownload = self.props.host+'/getfile/'+files[index].id;
                files[index].actionDownloadPDF = (files[index].converted) ? (self.props.host+'/getpdf/'+files[index].id) : '';
            }
            self.setState({files : files});
        });
    }
    render() {
        return (
            <div className="PDF-converter">
                <h3>API Host: {this.props.host}</h3>
                <ConverterFileList refresh={this.getFiles} host={this.props.host} files={this.state.files}/>
                <ConverterUploadForm refresh={this.getFiles} action={this.props.host+'/upload/'} accept={this.acceptTypes}/>
            </div>
        );
    }
}

export class ConverterFileList extends Component {
    render() {
        return (
            <div className="File-list col-md-8 col-md-offset-2">
                <div className="panel panel-default">
                    <div className="panel-heading">Uploads List</div>
                        <div className="panel-body uploads">
                            <ol>
                            {
                                this.props.files.length ? (
                                    this.props.files.map((file) =>
                                        <ConverterFileListRow refresh={this.props.refresh} host={this.props.host} file={file} key={file.id}/>
                                    )
                             ) : (<p>No files uploaded yet</p>)
                            }
                            </ol>
                            <p className="help-block">Note: rasterization only for small(2-3 pages) documents!</p>
                        </div>
                </div>
            </div>
        );
    }
}
export class ConverterFileListRow extends Component {
    constructor(props){
        super(props);
        this.state = {busy:false}
        this.Convert = this.Convert.bind(this);
        this.Rastr = this.Rastr.bind(this);
        this.Delete = this.Delete.bind(this);
    }
    Convert(){
        var self = this;
        this.setState({busy:true});
        axios.post(this.props.host+'/convert/'+this.props.file.id+'/true/false').then(function (response) {
            self.setState({busy:false});
            self.props.refresh();
        });
    }
    Rastr(){
        var self = this;
        this.setState({busy:true});
        axios.post(this.props.host+'/convert/'+this.props.file.id+'/true/true').then(function (response) {
            self.setState({busy:false});
            self.props.refresh();
        });
    }
    Delete()
    {
        var self = this;
        this.setState({busy:true});
        axios.post(this.props.host+'/deletefile/'+this.props.file.id).then(function (response) {
            self.setState({busy:false});
            self.props.refresh();
        });
    }
    render() {
        return (
            <li>
                <span className="filename">{this.props.file.name}</span>
                {!this.state.busy ?(
                        <span>
                            <ButtonLink label="Original" action={this.props.file.actionDownload} bclass="btn-primary" />
                            <ButtonLink label="PDF" action={this.props.file.actionDownloadPDF} bclass="btn-danger" />
                            <ButtonClick label="Convert" bclass="btn-success" click={this.Convert}/>
                            <ButtonClick label="Rastr"  bclass="btn-success" click={this.Rastr}/>
                            <ButtonClick label="Delete" bclass="btn-warning" click={this.Delete}/>
                        </span>
                    )
                    :(
                        <span>Processing...</span>
                    )
                }

            </li>
        );
    }
}
export class ButtonClick extends Component {
    render() {
        return (
            <button className={"btn "+this.props.bclass} onClick={this.props.click}>
                {this.props.label}
            </button>
        )
    }
}
export class ButtonLink extends Component {
    render() {
        return (
            <a href={(this.props.action) ? this.props.action : '#'} className={"btn "+this.props.bclass} disabled={(this.props.action) ? false : true}>
                {this.props.label}
            </a>
        )
    }
}
export class ConverterUploadForm extends Component {
    constructor(props) {
        super(props);
        this.state ={
            files:null,
            busy:false
        };
        this.onChange = this.onChange.bind(this);
        this.filesUpload = this.filesUpload.bind(this);
        this.onUpload = this.onUpload.bind(this);
        this.onUploadConvert = this.onUploadConvert.bind(this);
    }
    onFormSubmit(e){
        e.preventDefault(); // Stop form submit
        e.target.reset();
    }
    onUpload(){
        if(this.state.files){
            var self = this;
            this.filesUpload(this.state.files,false).then((response)=>{
                self.setState({busy:false});
                self.props.refresh();
            })
        }
    }
    onUploadConvert(){
        if(this.state.files) {
            var self = this;
            this.filesUpload(this.state.files, true).then((response)=> {
                self.setState({busy:false});
                self.props.refresh();
            })
        }
    }
    onChange(e) {
        this.setState({files:e.target.files})
    }
    filesUpload(files,convert){
        this.setState({busy:true});
        const url = this.props.action+convert;
        const formData = new FormData();
        Array.from(files).map(file =>
            formData.append('docs[]',file)
        );
        const config = {
            headers: {
                'content-type': 'multipart/form-data'
            }
        }
        return  axios.post(url, formData,config)
    }
    render() {
        return (
            <div className="File-list col-md-8 col-md-offset-2">
                {
                    !this.state.busy ? (
                        <form onSubmit={this.onFormSubmit} className="form-convert-upload">
                            <div className="form-group">
                                <input type="file" name="docs[]" accept={this.props.accept} multiple="multiple" onChange={this.onChange} required="required"/>
                                <p className="help-block">.doc, .docx, .xls, .xlsx, .ppt, .pptx formats</p>
                            </div>
                            <div className="form-group">
                                <input type="submit" name="submit" value="Upload" onClick={this.onUpload} className="btn btn-success"/>
                                <input type="submit" name="submit-convert" value="Upload and convert" onClick={this.onUploadConvert} className="btn btn-danger" />
                            </div>
                        </form>
                    ) : (
                        <div><img src={loader}/>Uploading in process</div>
                    )
                }
            </div>
        )
    }
}
