<?php if (get_plugin_options('contact_plugin_active')): ?>

<div>
    <div class="contact-form-success">
        <span id="form_success"></span>
    </div>
    <div id="form_error" class="contact-form-error">
        <span id="form_error"></span>
    </div>

    <form id="enquiry_form">

        <?php wp_nonce_field('wp_rest'); ?>
        
        <div>
            <label for="name">Name</label>
            <input name="name" id="name" type="text" placeholder="Name">
        </div>
        <div>
            <label for="email">Email address</label>
            <input name="email" id="email" type="text" placeholder="Email address">
        </div>
        <div>
            <label for="phone">Phone</label>
            <input name="phone" id="phone" type="text" placeholder="Phone">
        </div>
        <div>
            <label for="message">Message</label>
            <textarea name="message" id="message"></textarea>
        </div>

        <div>
            <button type="submit">Submit form</button>
        </div>

    </form>
</div>

<script>
    let enquiry_form = document.querySelector("#enquiry_form");

    enquiry_form.addEventListener("submit", (e) => {
        e.preventDefault();
        const formdata = new FormData(enquiry_form);
        let object = {};
        formdata.forEach((value, key) => {
            if(!Reflect.has(object, key)) {
                object[key] = value;
                return;
            }
            if(!Array.isArray(object[key])) {
                object[key] = [object[key]];    
            }
            object[key].push(value);
        });

        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onload = function() {
            if (xmlhttp.readyState === 4) {
                let parser = new DOMParser();
                let response = parser.parseFromString(xmlhttp.responseText, "text/html");
                enquiry_form.remove();
                if (xmlhttp.status === 200) {
                    document.querySelector("#form_success").innerText = response.firstChild.innerText;
                } else {
                    document.querySelector("#form_error").innerText = response.firstChild.innerText;
                }
            }
        }
        xmlhttp.open("POST", "<?= get_rest_url(null, 'v1/contact-form/submit'); ?>");
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("data="+JSON.stringify(object));
    });
</script>

<?php else: ?>

<div>
    This form is not active
</div>

<?php endif; ?>