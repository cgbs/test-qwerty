jQuery(function($){
    $('form.form-button.delete').on('submit',function(){
        return confirm('Are you sure to delete this file?');
    });
    $('form.form-convert').on('submit',function(){
       $('.loading-dialog').show();
    });
});