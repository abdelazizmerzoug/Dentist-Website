<?php
// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'dentalclinic');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch payment details with patient and appointment information
$sql = "
    SELECT 
        pay.paid_amount, 
        pay.description, 
        CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name, 
        patients.phone AS patient_phone, 
        appointments.appointment_date, 
        appointments.appointment_time
    FROM pay
    JOIN patients ON pay.id_patient = patients.id_patient
    JOIN appointments ON pay.id_appointments = appointments.id_appointment
";
$result = $conn->query($sql);
$payments = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Information</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
            margin: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: var(--hover-clr);
            margin-bottom: 20px;
        }

        .filter-container {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    margin-bottom: 20px;
    gap: 15px;
}

.filter-item {
    flex: 1;
    min-width: 200px;
}

.filter-item label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: var(--base-clr);
}

.filter-item input, .filter-item select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    color: #333;
    background-color: #f9f9f9;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease-in-out;
}

.filter-item input:focus, .filter-item select:focus {
    border-color: var(--hover-clr);
    box-shadow: 0 0 5px rgba(26, 52, 74, 0.4);
    outline: none;
}


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: var(--base-clr);
            color: var(--text-clr);
        }

        tr:hover {
            background-color: #f1f1f1;
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
    <h2>Payment Information</h2>
    <div class="filter-container">
    <div class="filter-item">
        <label for="nameFilter">Search by Patient Name:</label>
        <input type="text" id="nameFilter" placeholder="Type patient name...">
    </div>
    <div class="filter-item">
        <label for="phoneFilter">Search by Patient Phone:</label>
        <input type="text" id="phoneFilter" placeholder="Type phone number...">
    </div>
    <div class="filter-item">
        <label for="dateFilter">Search by Appointment Date:</label>
        <input type="date" id="dateFilter">
    </div>
    <div class="filter-item">
        <label for="doctorFilter">Search by Doctor:</label>
        <select id="doctorFilter">
            <option value="">--Select Doctor--</option>
            <!-- Populate doctor options dynamically -->
            <?php
            // Fetch doctors for the dropdown
            $conn = new mysqli('localhost', 'root', '', 'dentalclinic');
            $doctorQuery = "SELECT id_doctor, CONCAT(first_name, ' ', last_name) AS doctor_name FROM doctors";
            $doctorResult = $conn->query($doctorQuery);
            while ($doctor = $doctorResult->fetch_assoc()) {
                echo "<option value='{$doctor['doctor_name']}'>{$doctor['doctor_name']}</option>";
            }
            ?>
        </select>
    </div>
</div>



<table id="paymentsTable">
    <thead>
        <tr>
            <th>Patient Name</th>
            <th>Phone Number</th>
            <th>Appointment Date</th>
            <th>Appointment Time</th>
            <th>Doctor</th>
            <th>Paid Amount</th>
            <th>Description</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Populate table rows with payment data
        $sql = "
            SELECT 
                pay.paid_amount, 
                pay.description, 
                CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name, 
                patients.phone AS patient_phone, 
                appointments.appointment_date, 
                appointments.appointment_time,
                CONCAT(doctors.first_name, ' ', doctors.last_name) AS doctor_name
            FROM pay
            JOIN patients ON pay.id_patient = patients.id_patient
            JOIN appointments ON pay.id_appointments = appointments.id_appointment
            JOIN doctors ON appointments.id_doctor = doctors.id_doctor
        ";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['patient_name']}</td>";
                echo "<td>{$row['patient_phone']}</td>";
                echo "<td>{$row['appointment_date']}</td>";
                echo "<td>{$row['appointment_time']}</td>";
                echo "<td>{$row['doctor_name']}</td>";
                echo "<td>{$row['paid_amount']}</td>";
                echo "<td>{$row['description']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No data available</td></tr>";
        }
        ?>
    </tbody>
</table>


<script>
$(document).ready(function () {
    const table = $('#paymentsTable').DataTable();

    // Filter by patient name
    $('#nameFilter').on('keyup', function () {
        table.columns(1).search(this.value).draw();
    });

    // Filter by patient phone
    $('#phoneFilter').on('keyup', function () {
        table.columns(2).search(this.value).draw();
    });

    // Filter by appointment date
    $('#dateFilter').on('change', function () {
        table.columns(3).search(this.value).draw();
    });

    // Filter by doctor
    $('#doctorFilter').on('change', function () {
        table.columns(4).search(this.value).draw();
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
