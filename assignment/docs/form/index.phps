<?php     
    $xml_post = simplexml_load_file("../../currencies.xml");
    $get_codes_post = $xml_post->xpath("//cCode");

    // using for loop to fill an array thats used for dropdown box with the correct currency codes for post button
    foreach($get_codes_post as $post_code){
        $dropdown_array_post[] = (string) $post_code;
        }
    
    $xml_put_del = simplexml_load_file("../../rates.xml");
    $get_codes_put_del = $xml_put_del->xpath("//code");
    
    // using for loop to fill an array thats used for dropdown box with the correct currency codes for put and delete button
    foreach($get_codes_put_del as $put_del_code){
        $dropdown_array_put_del[] = (string) $put_del_code;
    }
    
?>

<!DOCTYPE html>
<html>
   <head>
        <link rel="stylesheet" type="text/css" href="interface_style.css">
        <title>Task C Interface</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script>
            // function is used to fill the dropdown box with the elements of the arrays $dropdown_array_post[] and $dropdown_array_del[]
            function select_fill(arr) {
                var codes = document.getElementById('code_dropdown');
                codes.options.length = 0;
                for(var i = 0; i< arr.length; i++ ) {
                codes.options[codes.options.length] = new Option( arr[i], arr[i]);
                }
            }
        </script>
        <script>
            // using ajax post to post the currency code from the dropdown box and the value of the selected radio input to the "crud.php" file to be processed and returned value from the file being put in textarea "text_box"
            $(document).ready(function(){
                $("button").click(function(){
                    var action = $("input[name='CRUD']:checked").val();
                    var code = $("#code_dropdown").val();
                    $.post('crud.php',{
                        codes:code,
                        actions:action
                    }, 
                    function(data){
                        $('#text_box').html(data);
                    });
                });    
            });
        </script>
   </head>
   <body>
        <h2>Conversion Interface</h2>

        <input type="radio" id="post" name="CRUD" value = "post" onclick='select_fill(<?php echo json_encode($dropdown_array_post);?>)'> Post
        <input type="radio" id="put" name="CRUD" value = "put" onclick='select_fill(<?php echo json_encode($dropdown_array_put_del);?>)'> Put
        <input type="radio" id="del" name="CRUD" value = "del" onclick='select_fill(<?php echo json_encode($dropdown_array_put_del);?>)'> Delete<br>
        
        <br><span id ="dropbox_text">Select Currency Code: </span><select id="code_dropdown"></select><br>

        <br><button type="button" id="button">Submit</button><br>
        <br><textarea id="text_box"></textarea>
   </body>
</html>