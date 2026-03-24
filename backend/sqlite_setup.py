import sqlite3

def init_db():
    # Connects to 'gym_app.db'. It will be created if it doesn't exist.
    conn = sqlite3.connect("gym_app.db")
    cursor = conn.cursor()

    # 1. Create the login table with just username and password
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS login (
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL
        )
    """)

    # (Optional) Clear existing test data so we don't get duplicates if run multiple times
    cursor.execute("DELETE FROM login")

    # 2. Add 3 test accounts using parameterized queries to prevent SQL injection
    test_accounts = [
        ("gym_bro_99", "ironlifter1"),
        ("cardio_queen", "runfast123"),
        ("newbie_gains", "password321")
    ]
    
    cursor.executemany("""
        INSERT INTO login (username, password)
        VALUES (?, ?)
    """, test_accounts)

    # Commit the changes to the database
    conn.commit()
    print("Database initialized and test accounts added successfully.\n")

    # 3. Display all the rows in the console
    cursor.execute("SELECT * FROM login")
    rows = cursor.fetchall()
    
    print("--- Login Table Data ---")
    for row in rows:
        # row[0] is the username, row[1] is the password
        print(f"Username: {row[0]} | Password: {row[1]}")

    # Close the connection
    conn.close()

# Run the function
init_db()
