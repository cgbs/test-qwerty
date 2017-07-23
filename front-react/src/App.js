import React, { Component } from 'react';
import { PDFConverter } from './PDFConverter';
import logo from './logo.svg';
import './App.css';

class App extends Component {
  render() {
    return (
      <div className="App">
        <div className="App-header">
          <img src={logo} className="App-logo" alt="logo" />
          <h2>PDF Converter</h2>
        </div>
        <div className="App-intro">
          <PDFConverter host="http://127.0.0.1:8000/api" />
        </div>
      </div>
    );
  }
}

export default App;
