(function () {

    var submit = document.getElementById('submit');

    submit.onclick = function (e) {
        e.preventDefault();
        submit.setAttribute('disabled', 'disabled');
        var min = document.getElementById('min').value;
        var max = document.getElementById('max').value;
        var quantity = document.getElementById('quantity').value;
        prepareAndSend(min, max, quantity);
    };

    function prepareAndSend(min, max, quantity) {

        send(min, max, quantity);
    }

    function send(min, max, quantity) {
        var formData = new FormData();
        formData.append('min', min);
        formData.append('max', max);
        formData.append('quantity', quantity);

        var xmlHttp;
        if (window.XMLHttpRequest) {
            xmlHttp = new XMLHttpRequest();
        } else {
            xmlHttp = new ActiveXObject('Microsoft.XMLHTTP');
        }
        xmlHttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                var answer = JSON.parse(this.responseText);
                console.log(answer);
                submit.removeAttribute('disabled');
            }
        };
        xmlHttp.open('POST', 'ajax.php', true);
        xmlHttp.send(formData);
    }

})();