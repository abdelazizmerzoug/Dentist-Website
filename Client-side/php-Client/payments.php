<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
             @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
:root {
    --base-clr: #2e6f85;
    --line-clr: #42434a;
    --hover-clr: #222533;
    --text-clr: #e6e6ef;
    --accent-clr: #5e63ff;
    --secondary-text-clr: #b0b3c1;
}

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html{
    font-family: Poppins, 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.5rem;
}

body{
    margin-right: 8px;
    min-height: 100vh;
    background-color:#ededed;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 1em;
}

#sidebar{
    height: 100vh;
    width: 265px;
    padding: 10px 1em;
    background-color: var(--base-clr);
    border-right: 1px solid var(--line-clr);

    position: sticky;
    top: 0;
    align-self: start;
    transition: 300ms ease-in-out;
    overflow: hidden;
    text-wrap: nowrap;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);

}

#sidebar.close{
    padding: 10px;
    width: 60px;
}

#sidebar ul{
    list-style: none;
}

#sidebar > ul > li:first-child{
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    .logo{
        font-weight: 600;
    }
}

#sidebar a, #sidebar .dropdown-btn, #sidebar .logo{
    border-radius: .5em;
    padding: .85em;
    text-decoration: none;
    color: var(--text-clr);
    display: flex;
    align-items: center;
    gap: 1em;
}

.dropdown-btn{
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    font: inherit;
    cursor: pointer;
}

#sidebar svg{
    flex-shrink: 0;
    fill: #ededed;
}

#sidebar a span, #sidebar .dropdown-btn span{
    flex-grow: 1;
}

#sidebar a:hover, #sidebar .dropdown-btn:hover{
    background-color: var(--hover-clr);
}

#sidebar .sub-menu{
display: grid;
grid-template-rows: 0fr;
transition: 300ms ease-in-out;

> div{
    overflow: hidden;
}
}

#sidebar .sub-menu.show{
    grid-template-rows: 1fr;
}

.dropdown-btn svg{
    transition: 200ms ease;
}

.rotate svg:last-child{
    rotate: 180deg;
}

#sidebar .sub-menu a{
    padding-left: 2em;
}

#toggle-btn{
    margin-left: auto;
    border: none;
    border-radius: .5em;
    background: none;
    cursor: pointer;

    svg{
        transition: rotate 150ms ease;
    }
}

#toggle-btn:hover{
    background-color: var(--hover-clr);
}

#content {
            margin-top: 2em;
            width: auto;
            max-height: 90vh;
            background-color: #ffffff;
            color: #1a344a;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
            overflow-y: auto;
        }

        h2 {
            color: #1a344a;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input, select, textarea, button {
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }
        input[readonly], textarea[readonly] {
            background-color: #f0f0f0;
        }
        textarea {
            height: 100px;
            resize: none;
        }
        button {
            background-color: #1b4b62;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #144052;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<nav id="sidebar">
    <ul>
      <li>
        <img src="../images/header-logo.png" style="width: 50px; height: 50px; margin-left: 10px ;" alt="">
        <span class="logo">Smile Haven</span>
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      <li>
        <a href="index.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Home</span>
        </a>
      </li>
      <li>
        <a href="dashboard.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M620-163 450-333l56-56 114 114 226-226 56 56-282 282Zm220-397h-80v-200h-80v120H280v-120h-80v560h240v80H200q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v200ZM480-760q17 0 28.5-11.5T520-800q0-17-11.5-28.5T480-840q-17 0-28.5 11.5T440-800q0 17 11.5 28.5T480-760Z"/></svg>
          <span>Gestion de Stock</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="../html-Client/add_product.php">Ajout un produit</a></li>
            <li><a href="../php-Client/Show_Product.php">Affiches les Produit</a></li>
            <li><a href="../php-Client/pursh_pic.php">Facture</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M680-119q-8 0-16-2t-15-7l-120-70q-14-8-21.5-21.5T500-249v-141q0-16 7.5-29.5T529-441l120-70q7-5 15-7t16-2q8 0 15.5 2.5T710-511l120 70q14 8 22 21.5t8 29.5v141q0 16-8 29.5T830-198l-120 70q-7 4-14.5 6.5T680-119ZM400-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM80-160v-112q0-33 17-62t47-44q51-26 115-44t141-18h14q6 0 12 2-8 18-13.5 37.5T404-360h-4q-71 0-127.5 18T180-306q-9 5-14.5 14t-5.5 20v32h252q6 21 16 41.5t22 38.5H80Zm320-400q33 0 56.5-23.5T480-640q0-33-23.5-56.5T400-720q-33 0-56.5 23.5T320-640q0 33 23.5 56.5T400-560Zm0-80Zm12 400Zm174-166 94 55 94-55-94-54-94 54Zm124 208 90-52v-110l-90 53v109Zm-150-52 90 53v-109l-90-53v109Z"/></svg>          <span>Fournisseurs</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="../html-Client/Supplier.html">Ajout un Fournisseur</a></li>
            <li><a href="Show_Fournisseur.php">Afficher les fournisseur</a></li>
          </div>
        </ul>
      </li>
      <li>
        <a href="Show_Appoitments.php">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M600-80v-80h160v-400H200v160h-80v-320q0-33 23.5-56.5T200-800h40v-80h80v80h320v-80h80v80h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H600ZM320 0l-56-56 103-104H40v-80h327L264-344l56-56 200 200L320 0ZM200-640h560v-80H200v80Zm0 0v-80 80Z"/></svg>
          <span>Appoitments</span>
        </a>
      </li>
      <li>
        <a href="payments.php">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M560-440q-50 0-85-35t-35-85q0-50 35-85t85-35q50 0 85 35t35 85q0 50-35 85t-85 35ZM280-320q-33 0-56.5-23.5T200-400v-320q0-33 23.5-56.5T280-800h560q33 0 56.5 23.5T920-720v320q0 33-23.5 56.5T840-320H280Zm80-80h400q0-33 23.5-56.5T840-480v-160q-33 0-56.5-23.5T760-720H360q0 33-23.5 56.5T280-640v160q33 0 56.5 23.5T360-400Zm440 240H120q-33 0-56.5-23.5T40-240v-440h80v440h680v80ZM280-400v-320 320Z"/></svg>
        <span>Payment Management</span>
        </a>
      </li>
      <li>
        <a href="historique.php" class="active">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#5f6368"><path d="M80-200v-80h400v80H80Zm0-200v-80h200v80H80Zm0-200v-80h200v80H80Zm744 400L670-354q-24 17-52.5 25.5T560-320q-83 0-141.5-58.5T360-520q0-83 58.5-141.5T560-720q83 0 141.5 58.5T760-520q0 29-8.5 57.5T726-410l154 154-56 56ZM560-400q50 0 85-35t35-85q0-50-35-85t-85-35q-50 0-85 35t-35 85q0 50 35 85t85 35Z"/></svg>
        <span>Historique</span>
        </a>
      </li>
      <li>
        <a href="../php-Client/dynamic-full-calendar.php" class="active">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
          <span>Calendar</span>
        </a>
      </li>
      <li >
        <a href="profile.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-240v-32q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v32q0 33-23.5 56.5T720-160H240q-33 0-56.5-23.5T160-240Zm80 0h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </nav>

<div id="content">
    <h2>Payment Management</h2>
    <div class="form-container">
        <?php
        $error = "";
        $success = "";

        // Connect to the database
        $conn = new mysqli('localhost', 'root', '', 'dentalclinic');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if form data is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['appointment_id']) && !empty($_POST['patient_id']) && !empty($_POST['paid_amount'])) {
                $appointment_id = $_POST['appointment_id'];
                $patient_id = $_POST['patient_id'];
                $paid_amount = $_POST['paid_amount'];
                $description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : null;

                // Validate appointment exists and is confirmed
                $checkAppointment = $conn->query("SELECT * FROM appointments WHERE id_appointment = '$appointment_id' AND confirmation = 1");
                if ($checkAppointment->num_rows == 0) {
                    $error = "Error: Appointment not found or not confirmed.";
                } else {
                    // Insert payment into the database
                    $sql = "INSERT INTO pay (id_appointments, id_patient, paid_amount, description) VALUES ('$appointment_id', '$patient_id', '$paid_amount', '$description')";
                    if ($conn->query($sql) === TRUE) {
                        $success = "Payment added successfully.";
                    } else {
                        $error = "Error: " . $conn->error;
                    }
                }
            } else {
                $error = "Error: Missing required fields.";
            }
        }

        $conn->close();
        ?>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Payment Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="appointment_id">Select Appointment:</label>
                <select name="appointment_id" id="appointment_id" required>
                    <option value="">-- Select Appointment --</option>
                    <?php
                    // Reconnect to fetch dropdown data
                    $conn = new mysqli('localhost', 'root', '', 'dentalclinic');
                    $appointments = $conn->query("SELECT id_appointment, appointment_date, appointment_time, id_patient FROM appointments WHERE confirmation = 1");
                    while ($row = $appointments->fetch_assoc()) {
                        echo "<option value='{$row['id_appointment']}' data-patient-id='{$row['id_patient']}'>Appointment ID: {$row['id_appointment']} ({$row['appointment_date']} at {$row['appointment_time']})</option>";
                    }
                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="patient_id">Select Patient:</label>
                <select name="patient_id" id="patient_id" required>
                    <option value="">-- Select Patient --</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" readonly>
            </div>
            <div class="form-group">
                <label for="paid_amount">Payment Amount:</label>
                <input type="number" name="paid_amount" id="paid_amount" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" placeholder="Enter description..."></textarea>
            </div>
            <button type="submit">Add Payment</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#appointment_id').on('change', function () {
        const patientId = $(this).find(':selected').data('patient-id');
        if (patientId) {
            $.ajax({
                url: 'get_patient.php',
                method: 'POST',
                data: { patient_id: patientId },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('#patient_id').html(data.patientOptions);
                        $('#phone_number').val(data.phoneNumber);
                    } else {
                        $('#patient_id').html('<option value="">-- Select Patient --</option>');
                        $('#phone_number').val('');
                    }
                },
            });
        } else {
            $('#patient_id').html('<option value="">-- Select Patient --</option>');
            $('#phone_number').val('');
        }
    });
});
</script>
<script>
    const toggleButton = document.getElementById('toggle-btn')
const sidebar = document.getElementById('sidebar')

function toggleSidebar(){
  sidebar.classList.toggle('close')
  toggleButton.classList.toggle('rotate')

  closeAllSubMenus()
}

function toggleSubMenu(button){

  if(!button.nextElementSibling.classList.contains('show')){
    closeAllSubMenus()
  }

  button.nextElementSibling.classList.toggle('show')
  button.classList.toggle('rotate')

  if(sidebar.classList.contains('close')){
    sidebar.classList.toggle('close')
    toggleButton.classList.toggle('rotate')
  }
}

function closeAllSubMenus(){
  Array.from(sidebar.getElementsByClassName('show')).forEach(ul => {
    ul.classList.remove('show')
    ul.previousElementSibling.classList.remove('rotate')
  })
}
</script>
</body>
</html>
