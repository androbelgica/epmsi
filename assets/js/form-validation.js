$(document).ready(function() {
    $.validator.addMethod("noSpacesOnly", function(value, element) {
        return value.trim() !== '';
    }, "Please enter a non-empty value");

    $.validator.addMethod("lettersonly", function(value, element) {
        return /^[a-zA-Z\s]*$/.test(value);
    }, "Please enter alphabet characters only");

    $.validator.addMethod("noDigits", function(value, element) {
        return !/\d/.test(value);
    }, "Please enter a value without digits");

    $.validator.addMethod("validStatus", function(value, element) {
        return value === "Active" || value === "Inactive";
    }, "Please select a valid status");

    $('#add_client').validate({
        rules: {
            clientName: {
                required: true,
                noSpacesOnly: true,
                lettersonly: true
            },
            clientEmail: {
                required: true,
                email: true
            },
            clientPhone: {
                required: true,
                noSpacesOnly: true,
                digits: true,
                maxlength: 10,
                minlength: 10
            },
            clientAddress: {
                required: true,
                noSpacesOnly: true
            },
            clientStatus: {
                required: true,
                validStatus: true
            }
        },
        messages: {
            clientName: {
                required: "Please enter a client name",
                lettersonly: "Only alphabet characters are allowed"
            },
            clientEmail: {
                required: "Please enter a client email",
                email: "Please enter a valid email address"
            },
            clientPhone: {
                required: "Please enter a client phone number",
                digits: "Client phone number should contain only digits",
                maxlength: "Client phone number should be 10 digits long",
                minlength: "Client phone number should be 10 digits long"
            },
            clientAddress: {
                required: "Please enter a client address"
            },
            clientStatus: {
                required: "Please select a client status",
                validStatus: "Please select a valid status"
            }
        },
        submitHandler: function(form) {
            form.submit();
        }
    });
});
