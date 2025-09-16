<!DOCTYPE html>
<html>

<head>
    <title>New Admission - Hostel Management</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        padding: 20px;
    }

    .form-container {
        max-width: 800px;
        margin: auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
    }

    h2 {
        text-align: center;
        color: #007bff;
    }

    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        margin-top: 20px;
        background: green;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <div class="form-container">
        <h2>New Admission</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Full Name</label><input type="text" name="full_name" required>
            <label>Name of Father</label><input type="text" name="father_name">
            <label>Name of Mother</label><input type="text" name="mother_name">
            <label>Mobile Number</label><input type="text" name="mobile" required>
            <label>Date of Birth</label><input type="date" name="dob">
            <label>Blood Group</label><input type="text" name="blood_group">
            <label>Email</label><input type="email" name="email" required>
            <label>Photo</label><input type="file" name="photo" required>
            <label>Addhar Photo</label><input type="file" name="photo" required>
            <label>Name of Institute</label><input type="text" name="institute">
            <label>Department</label><input type="text" name="department">
            <label>Name of Course</label><input type="text" name="course">
            <label>Permanent Address</label>
            <input type="text" name="village" placeholder="village">
            <input type="text" name="Post" placeholder="Post">
            <input type="text" name="Pin" placeholder="Pin">
            <input class="tal" type="taluka" name="taluka" placeholder="taluka">
            <label for="taluka">Select Taluka</label>
            <select id="taluka" name="taluka" required>
                <option value="">Select Taluka</option>
                <optgroup label="Ahmedabad">
                    <option>Daskroi</option>
                    <option>Dholka</option>
                    <option>Dhandhuka</option>
                    <option>Sanand</option>
                    <option>Viramgam</option>
                    <option>Detroj-Rampura</option>
                    <option>Ahmedabad City</option>
                </optgroup>
                <optgroup label="Gandhinagar">
                    <option>Gandhinagar</option>
                    <option>Kalol</option>
                    <option>Mansa</option>
                    <option>Dehgam</option>
                </optgroup>
                <optgroup label="Surat">
                    <option>Bardoli</option>
                    <option>Kamrej</option>
                    <option>Mandvi</option>
                    <option>Olpad</option>
                    <option>Palsana</option>
                    <option>Songadh</option>
                    <option>Uchchhal</option>
                    <option>Vyara</option>
                    <option>Surat City</option>
                </optgroup>
                <optgroup label="Rajkot">
                    <option>Rajkot</option>
                    <option>Gondal</option>
                    <option>Jetpur</option>
                    <option>Jamkandorna</option>
                    <option>Upleta</option>
                    <option>Dhoraji</option>
                    <option>Wankaner</option>
                </optgroup>
                <optgroup label="Vadodara">
                    <option>Vadodara</option>
                    <option>Dabhoi</option>
                    <option>Karjan</option>
                    <option>Padra</option>
                    <option>Savli</option>
                    <option>Waghodia</option>
                </optgroup>
                <optgroup label="Bhavnagar">
                    <option>Bhavnagar</option>
                    <option>Sihor</option>
                    <option>Palitana</option>
                    <option>Mahuva</option>
                    <option>Talaja</option>
                    <option>Gadhada</option>
                </optgroup>
                <optgroup label="Jamnagar">
                    <option>Jamnagar</option>
                    <option>Kalavad</option>
                    <option>Lalpur</option>
                    <option>Jamjodhpur</option>
                    <option>Jodiya</option>
                </optgroup>
                <optgroup label="Kutch">
                    <option>Bhuj</option>
                    <option>Anjar</option>
                    <option>Mandvi</option>
                    <option>Mundra</option>
                    <option>Nakhatrana</option>
                    <option>Rapar</option>
                </optgroup>
                <optgroup label="Mehsana">
                    <option>Mehsana</option>
                    <option>Kadi</option>
                    <option>Visnagar</option>
                    <option>Unjha</option>
                    <option>Vijapur</option>
                    <option>Satlasana</option>
                    <option>Becharaji</option>
                </optgroup>
                <optgroup label="Banaskantha">
                    <option>Palanpur</option>
                    <option>Danta</option>
                    <option>Vadgam</option>
                    <option>Amirgadh</option>
                    <option>Dhanera</option>
                    <option>Deesa</option>
                    <option>Tharad</option>
                    <option>Lakhani</option>
                    <option>Bhabhar</option>
                    <option>Vav</option>
                    <option>Kankrej</option>
                </optgroup>
                <optgroup label="Patan">
                    <option>Patan</option>
                    <option>Sidhpur</option>
                    <option>Harij</option>
                    <option>Sami</option>
                    <option>Radhanpur</option>
                    <option>Chanasma</option>
                </optgroup>
                <optgroup label="Sabarkantha">
                    <option>Himmatnagar</option>
                    <option>Idar</option>
                    <option>Khedbrahma</option>
                    <option>Vijaynagar</option>
                    <option>Talod</option>
                    <option>Prantij</option>
                    <option>Bayad</option>
                </optgroup>
                <optgroup label="Aravalli">
                    <option>Modasa</option>
                    <option>Malpur</option>
                    <option>Dhansura</option>
                    <option>Meghraj</option>
                    <option>Bhiloda</option>
                </optgroup>
                <optgroup label="Anand">
                    <option>Anand</option>
                    <option>Petlad</option>
                    <option>Sojitra</option>
                    <option>Tarapur</option>
                    <option>Umreth</option>
                    <option>Khambhat</option>
                    <option>Borsad</option>
                </optgroup>
                <optgroup label="Kheda">
                    <option>Nadiad</option>
                    <option>Kapadvanj</option>
                    <option>Mahudha</option>
                    <option>Matar</option>
                    <option>Mehmedabad</option>
                    <option>Thasra</option>
                    <option>Virpur</option>
                </optgroup>
                <optgroup label="Panchmahal">
                    <option>Godhra</option>
                    <option>Kalol</option>
                    <option>Halol</option>
                    <option>Lunawada</option>
                    <option>Morwa Hadaf</option>
                    <option>Santrampur</option>
                </optgroup>
                <optgroup label="Dahod">
                    <option>Dahod</option>
                    <option>Jhalod</option>
                    <option>Fatepura</option>
                    <option>Garbada</option>
                    <option>Dhanpur</option>
                    <option>Devgadh Baria</option>
                </optgroup>
                <optgroup label="Mahisagar">
                    <option>Lunawada</option>
                    <option>Balasinor</option>
                    <option>Virpur</option>
                    <option>Khanpur</option>
                    <option>Kadana</option>
                </optgroup>
                <optgroup label="Chhota Udaipur">
                    <option>Chhota Udaipur</option>
                    <option>Jetpur Pavi</option>
                    <option>Kawant</option>
                    <option>Naswadi</option>
                    <option>Sankheda</option>
                    <option>Bodeli</option>
                </optgroup>
                <optgroup label="Narmada">
                    <option>Rajpipla</option>
                    <option>Dediapada</option>
                    <option>Tilakwada</option>
                    <option>Garudeshwar</option>
                </optgroup>
                <optgroup label="Bharuch">
                    <option>Bharuch</option>
                    <option>Ankleshwar</option>
                    <option>Jambusar</option>
                    <option>Vagra</option>
                    <option>Amod</option>
                    <option>Hansot</option>
                </optgroup>
                <optgroup label="Navasari">
                    <option>Navsari</option>
                    <option>Gandevi</option>
                    <option>Chikhli</option>
                    <option>Khergam</option>
                    <option>Jalalpore</option>
                </optgroup>
                <optgroup label="Valsad">
                    <option>Valsad</option>
                    <option>Pardi</option>
                    <option>Umbergaon</option>
                    <option>Kaprada</option>
                    <option>Dharampur</option>
                </optgroup>
                <optgroup label="Tapi">
                    <option>Vyara</option>
                    <option>Songadh</option>
                    <option>Nizar</option>
                    <option>Uchchhal</option>
                    <option>Valod</option>
                    <option>Dolvan</option>
                </optgroup>
                <optgroup label="Botad">
                    <option>Botad</option>
                    <option>Gadhada</option>
                    <option>Barwala</option>
                    <option>Ranpur</option>
                </optgroup>
                <optgroup label="Morbi">
                    <option>Morbi</option>
                    <option>Maliya</option>
                    <option>Tankara</option>
                    <option>Wankaner</option>
                    <option>Halvad</option>
                </optgroup>
                <optgroup label="Devbhumi Dwarka">
                    <option>Khambhalia</option>
                    <option>Bhanvad</option>
                    <option>Kalyanpur</option>
                    <option>Dwarka</option>
                </optgroup>
                <optgroup label="Gir Somnath">
                    <option>Veraval</option>
                    <option>Kodinar</option>
                    <option>Talala</option>
                    <option>Sutrapada</option>
                    <option>Gir Gadhada</option>
                </optgroup>
                <optgroup label="Amreli">
                    <option>Amreli</option>
                    <option>Lathi</option>
                    <option>Savarkundla</option>
                    <option>Khambha</option>
                    <option>Jafrabad</option>
                    <option>Rajula</option>
                    <option>Babra</option>
                </optgroup>
                <optgroup label="Junagadh">
                    <option>Junagadh</option>
                    <option>Manavadar</option>
                    <option>Malia</option>
                    <option>Mangrol</option>
                    <option>Visavadar</option>
                    <option>Keshod</option>
                    <option>Bhesan</option>
                </optgroup>
                <optgroup label="Porbandar">
                    <option>Porbandar</option>
                    <option>Ranavav</option>
                    <option>Kutiyana</option>
                </optgroup>
            </select>
            <input type="text" name="District" placeholder="District">
            <label for="stateCode">Select State Code</label>

            <select id="stateCode" name="state_code" required>
                <option value="">Select State Code</option>
                <option value="AP">Andhra Pradesh</option>
                <option value="AR">Arunachal Pradesh</option>
                <option value="AS">Assam</option>
                <option value="BR">Bihar</option>
                <option value="CG">Chhattisgarh</option>
                <option value="GA">Goa</option>
                <option value="GJ">Gujarat</option>
                <option value="HR">Haryana</option>
                <option value="HP">Himachal Pradesh</option>
                <option value="JK">Jammu & Kashmir</option>
                <option value="JH">Jharkhand</option>
                <option value="KA">Karnataka</option>
                <option value="KL">Kerala</option>
                <option value="MP">Madhya Pradesh</option>
                <option value="MH">Maharashtra</option>
                <option value="MN">Manipur</option>
                <option value="ML">Meghalaya</option>
                <option value="MZ">Mizoram</option>
                <option value="NL">Nagaland</option>
                <option value="OD">Odisha</option>
                <option value="PB">Punjab</option>
                <option value="RJ">Rajasthan</option>
                <option value="SK">Sikkim</option>
                <option value="TN">Tamil Nadu</option>
                <option value="TS">Telangana</option>
                <option value="TR">Tripura</option>
                <option value="UP">Uttar Pradesh</option>
                <option value="UK">Uttarakhand</option>
                <option value="WB">West Bengal</option>
                <option value="AN">Andaman & Nicobar</option>
                <option value="CH">Chandigarh</option>
                <option value="DN">Dadra & Nagar Haveli</option>
                <option value="DD">Daman & Diu</option>
                <option value="DL">Delhi</option>
                <option value="LD">Lakshadweep</option>
                <option value="PY">Puducherry</option>
            </select>
            <input type="text" name="Country" placeholder="Country">
            <!-- <option value="" disabled selected>City</option> -->
            <!-- <textarea name="address" rows="3"></textarea> -->
            <label>Present Address</label><textarea name="address" rows="3"></textarea>
            <label>Local Guardian Name</label><input type="text" name="guardian_name">
            <label>Local Guardian Mobail No</label><input type="text" name="guardian_contact">
            <!-- <label>Nationality</label><input type="text" name="nationality"> -->
            <!-- <label>Passport No</label><input type="text" name="passport"> -->
            <!-- <label>Religion</label><input type="text" name="religion"> -->
            <label>Student Web Login Id</label><input type="text" name="login_id" required>
            <label>Password</label><input type="password" name="password" required>
            <label>Confirm Password</label><input type="password" name="confirm_password" required>
            <!-- <label>National ID</label><input type="text" name="national_id"> -->
            <button type="submit">Save</button>
        </form>
    </div>
</body>

</html>
<!-- <label>Cours</label><input type="text" name="program"> -->