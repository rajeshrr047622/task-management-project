<?php
$servername = "localhost";
$username = "root";
$password = "Rajesh@122";
$dbname = "task_manager";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error and success messages
$errorMessage = '';
$successMessage = '';

// Handle form submission for updating a task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_task'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);
    
    // Validation
    if (empty($title)) {
        $errorMessage = 'Title is required.';
    } elseif (empty($description)) {
        $errorMessage = 'Description is required.';
    } elseif (!in_array($status, ['pending', 'completed'])) {
        $errorMessage = 'Invalid status.';
    } else {
        // Escape special characters for SQL query
        $title = $conn->real_escape_string($title);
        $description = $conn->real_escape_string($description);
        $status = $conn->real_escape_string($status);

        // Update task
        $sql = "UPDATE tasks SET title='$title', description='$description', status='$status' WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            $successMessage = 'Task updated successfully.';
            // Display success message for 10 seconds and then redirect to task_list.php
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'task_list.php';
                    }, 1500); // 1.5 seconds delay
                  </script>";
        } else {
            $errorMessage = 'Error updating task: ' . $conn->error;
        }
    }
}

// Fetch task details for editing
$editTask = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $sql = "SELECT * FROM tasks WHERE id=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $editTask = $result->fetch_assoc();
    } else {
        $errorMessage = 'Task not found.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <style>
        /* General Body and Layout */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #eef2f3; /* Soft light background */
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background: #ffffff; /* White background for container */
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1); /* Deeper shadow */
            border: 1px solid #ddd; /* Light border */
        }

        /* Headings */
        h1 {
            color: #333;
            font-size: 2.4em;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #444;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 14px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa; /* Very light gray for inputs */
        }

        textarea {
            resize: vertical; /* Allows vertical resizing */
        }

        button {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            background-color: #28a745; /* Green button */
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1); /* Light shadow for button */
        }

        button:hover {
            background-color: #218838; /* Darker green on hover */
            transform: translateY(-2px); /* Slight lift effect */
        }

        /* Error/Success Messages */
        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Task</h1>
        <?php if ($errorMessage): ?>
            <div class="message error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php elseif ($successMessage): ?>
            <div class="message success">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
          
        <?php endif; ?>

        <?php if ($editTask): ?>
            <form method="POST" action="edit_task.php">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($editTask['id']); ?>">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($editTask['title']); ?>" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($editTask['description']); ?></textarea>
                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="pending" <?php echo $editTask['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $editTask['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
                <button type="submit" name="update_task">Submit</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
