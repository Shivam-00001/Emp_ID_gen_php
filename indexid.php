<!-- <?php include('db.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        .id-card {
            width: 100%;
            max-width: 400px;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: auto;
            margin-bottom: 20px;
            position: relative; 
        }

        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            overflow: hidden;
        }

        .circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .id-card h5 {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .id-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .barcode {
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .id-card {
                max-width: 100%;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Employee ID Generator</h2>

    <!-- Form to add employee -->
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="mobile" class="form-label">Mobile Number</label>
            <input type="text" class="form-control" id="mobile" name="mobile" required>
        </div>
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" name="generate" class="btn btn-primary w-100">Generate Employee ID</button>
    </form>

    <?php
    if (isset($_POST['generate'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $dob = $_POST['dob'];
        $imagePath = "";

        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
            $imagePath = $targetDir . $imageName;

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    echo "File uploaded successfully.<br>";
                } else {
                    echo "Error uploading the file.<br>";
                    $imagePath = "";
                }
            } else {
                echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.<br>";
                $imagePath = "";
            }
        }

        $employeeID = "EMP" . mt_rand(100000, 999999);

        $stmt = $conn->prepare("INSERT INTO employees (employee_id, name, email, mobile, dob, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $employeeID, $name, $email, $mobile, $dob, $imagePath);

        if ($stmt->execute()) {
            echo "Employee created successfully. ID: " . $employeeID;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
    ?>

    <h3 class="mt-5">Employee List</h3>

    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM employees");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="id-card">
                        <div class="circle">
                            <?php if ($row['image_path']): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Profile Image">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo $row['employee_id']; ?></h5>
                        <h5><?php echo $row['name']; ?></h5>
                        <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                        <p><strong>Mobile:</strong> <?php echo $row['mobile']; ?></p>
                        <p><strong>DOB:</strong> <?php echo $row['dob']; ?></p>

                        <div class="barcode">
                            <svg id="barcode<?php echo $row['id']; ?>"></svg>
                            <script>
                                JsBarcode("#barcode<?php echo $row['id']; ?>", "<?php echo $row['employee_id']; ?>", {
                                    format: "CODE128", 
                                    lineColor: "#000000",  
                                    width: 2,  
                                    height: 60, 
                                    displayValue: true 
                                });
                            </script>
                        </div>

                        <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No employees found.</p>";
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> -->



<?php include('db.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        .id-card {
            width: 100%;
            max-width: 400px;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 20px;
            background-color: #f8f9fa;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: auto;
            margin-bottom: 20px;
            position: relative; 
        }

        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
            overflow: hidden;
        }

        .circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .id-card h5 {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }

        .id-card p {
            margin: 5px 0;
            font-size: 14px;
            color: #333;
        }

        .barcode {
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .id-card {
                max-width: 100%;
            }
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
            }
            .print-area {
                position: absolute;
                top: 0;
                left: 0;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Employee ID Generator</h2>

    <!-- Form to add employee -->
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="mobile" class="form-label">Mobile Number</label>
            <input type="text" class="form-control" id="mobile" name="mobile" required>
        </div>
        <div class="mb-3">
            <label for="dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Upload Image</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" name="generate" class="btn btn-primary w-100">Generate Employee ID</button>
    </form>

    <!-- Upload CSV Form -->
    <h3 class="mt-5">Upload CSV to Generate Employee IDs</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="csv_file" class="form-label">Upload CSV File</label>
            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
        </div>
        <button type="submit" name="upload_csv" class="btn btn-success w-100">Upload CSV</button>
    </form>

    <?php
    if (isset($_POST['generate'])) {
        // Process the individual employee form (existing code)
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $dob = $_POST['dob'];
        $imagePath = "";

        if (!empty($_FILES['image']['name'])) {
            $targetDir = "uploads/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
            $imagePath = $targetDir . $imageName;

            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (in_array($_FILES['image']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    echo "File uploaded successfully.<br>";
                } else {
                    echo "Error uploading the file.<br>";
                    $imagePath = "";
                }
            } else {
                echo "Invalid file type. Only JPEG, PNG, and GIF are allowed.<br>";
                $imagePath = "";
            }
        }

        $employeeID = "EMP" . mt_rand(100000, 999999);

        $stmt = $conn->prepare("INSERT INTO employees (employee_id, name, email, mobile, dob, image_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $employeeID, $name, $email, $mobile, $dob, $imagePath);

        if ($stmt->execute()) {
            echo "Employee created successfully. ID: " . $employeeID;
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    if (isset($_POST['upload_csv'])) {
        // Process the uploaded CSV file
        $csvFile = $_FILES['csv_file']['tmp_name'];

        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Skip the first line (header row)
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Check if CSV row has sufficient columns (name, email, mobile, dob)
                if (count($data) < 4) {
                    echo "Skipping invalid row: " . implode(", ", $data) . "<br>";
                    continue; // Skip this row if it doesn't have enough columns
                }

                // Extract the data from the CSV
                $name = $data[0];
                $email = $data[1];
                $mobile = $data[2];
                $dob = $data[3];
                $imagePath = "";  // Optional: Add logic for default image if necessary

                // Check if the email is empty (if it is, skip the row)
                if (empty($email)) {
                    echo "Skipping row with missing email: " . implode(", ", $data) . "<br>";
                    continue;
                }

                // Generate Employee ID
                $employeeID = "EMP" . mt_rand(100000, 999999);

                // Insert into the database
                $stmt = $conn->prepare("INSERT INTO employees (employee_id, name, email, mobile, dob, image_path) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $employeeID, $name, $email, $mobile, $dob, $imagePath);

                if ($stmt->execute()) {
                    echo "Employee created successfully. ID: " . $employeeID . "<br>";
                } else {
                    echo "Error: " . $stmt->error . "<br>";
                }

                $stmt->close();
            }

            fclose($handle);
        }
    }
    ?>

    <h3 class="mt-5">Employee List</h3>

    <div class="row">
        <?php
        $result = $conn->query("SELECT * FROM employees");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Convert dob from yyyy-mm-dd to dd-mm-yyyy format
                $dob = new DateTime($row['dob']);
                $dobFormatted = $dob->format('d-m-Y'); // dd-mm-yyyy format
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="id-card">
                        <div class="circle">
                            <?php if ($row['image_path']): ?>
                                <img src="<?php echo $row['image_path']; ?>" alt="Profile Image">
                            <?php else: ?>
                                <span>No Image</span>
                            <?php endif; ?>
                        </div>
                        <h5><?php echo $row['employee_id']; ?></h5>
                        <h5><?php echo $row['name']; ?></h5>
                        <p><strong>Email:</strong> <?php echo $row['email']; ?></p>
                        <p><strong>Mobile:</strong> <?php echo $row['mobile']; ?></p>
                        <p><strong>DOB:</strong> <?php echo $dobFormatted; ?></p>

                        <div class="barcode">
                            <svg id="barcode<?php echo $row['id']; ?>"></svg>
                            <script>
                                JsBarcode("#barcode<?php echo $row['id']; ?>", "<?php echo $row['employee_id']; ?>", {
                                    format: "CODE128", 
                                    lineColor: "#000000",  
                                    width: 2,  
                                    height: 60, 
                                    displayValue: true 
                                });
                            </script>
                        </div>

                        <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>No employees found.</p>";
        }
        ?>
    </div>
</div>
</body>
</html>
