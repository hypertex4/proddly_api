<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Proddly: Homepage</title>
</head>
<body>
    <h1>PRODDLY: Home Page</h1>

    <form id="paymentForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email-address" required />
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="tel" id="amount" value="9950" readonly required />
        </div>
        <div class="form-group">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" />
        </div>
        <div class="form-group">
            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" />
        </div>
        <div class="form-group">
            <label for="store-id">Store ID</label>
            <input type="text" id="store-id" value="6970367" />
        </div>
        <div class="form-group">
            <label for="plan">Subscription Plan</label>
            <div id="proddly_plan">

            </div>
        </div>
        <div class="form-submit">
            <button type="submit" onclick="payWithPaystack()"> Pay </button>
        </div>
    </form>

    <script type = "text/javascript" src = "https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener("submit", payWithPaystack, false);
        function payWithPaystack(e) {
        e.preventDefault();
        let handler = PaystackPop.setup({
            key: 'pk_test_a9126bfe168fb3b614b1723bdfa1f732ecf8bdae',
            email: document.getElementById("email-address").value,
            firstname: document.getElementById("first-name").value,
            lastname: document.getElementById("last-name").value,
            amount: document.getElementById("amount").value * 100,
            ref: ''+Math.floor((Math.random() * 1000000000) + 1),
            "metadata":{
                "store_id":document.getElementById("store-id").value,
                "custom_fields":[
                    {
                    "display_name":"Store ID",
                    "variable_name":"store_id",
                    "value":document.getElementById("store-id").value
                    }
                ]
            },
            onClose: function(){
                alert('Window closed.');
            },
            callback: function(response){
                let message = 'Payment complete! Reference: ' + response.reference;
                Initialize(response.reference,document.getElementById("store-id").value,$('input[name="p_plan"]:checked').val());
            }
        });
        handler.openIframe();
        }

        function Initialize(ref_id,store_id,p_plan) {
            $.ajax({
                url: "http://localhost/proddly_api/v1/api/store-payment",type:"POST",contentType : 'application/json',dataType: 'json',
                data:JSON.stringify({ref_id,store_id,p_plan}),
                success: function (data){
                    console.log(data);
                     alert(data.msg);
                }, 
                error: function () { console.log("Error");}
            });
        }
         
    </script>
    <script>
        const divPlan = document.getElementById('proddly_plan');
        
        $.ajax({
            url: "http://localhost/proddly_api/v1/api/get-plans",type:"GET",dataType: 'json',
            headers: {"Developer_Key": "GJlXKBL9cFAMB41"},
            success: function (res){
                var obj = res.data;

                console.log(obj);

                html = '';
                
                $.each(obj, function(i, val) {
                    html += '<div class="form-group">';
                    html += '<input type="radio" id="'+val.plan_code+'" value="'+val.plan_code+'" name="p_plan"><label for="'+val.plan_code+'">'+val.name+' (#'+val.amount+')</label>';
                     html += '</div>';

                })

                $('#proddly_plan').html(html);
            }, 
            error: function () { console.log("Error");}
        });
    </script>
    <script>
        
    </script>
</body>
</html>