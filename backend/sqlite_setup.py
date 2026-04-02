import sqlite3

def init_db():
    conn = sqlite3.connect("gym_app.db")
    cursor = conn.cursor()

    # Enable Foreign Key support in SQLite
    cursor.execute("PRAGMA foreign_keys = ON;")

    # 1. User Table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS User (
            username TEXT PRIMARY KEY,
            password TEXT NOT NULL,
            name TEXT,
            img_url TEXT,
            weight REAL,
            height REAL,
            skill_level TEXT,
            DoB TEXT,
            bench_max REAL,
            squat_max REAL,
            deadlift_max REAL
        )
    """)

    # 2. Activities Table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Activities (
            activity_name TEXT PRIMARY KEY,
            main_muscle_group TEXT,
            secondary_muscle_group TEXT,
            description TEXT,
            youtube_link TEXT,
            set_text TEXT
        )
    """)

    # 3. Workout Table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Workout (
            workout_Id INTEGER PRIMARY KEY AUTOINCREMENT,
            workout_type TEXT,
            time_stamp DATETIME DEFAULT CURRENT_TIMESTAMP,
            username TEXT,
            FOREIGN KEY (username) REFERENCES User(username)
        )
    """)

    # 4. Lift Table
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Lift (
            liftID INTEGER PRIMARY KEY AUTOINCREMENT,
            workoutID INTEGER,
            activity_name TEXT,
            num_sets INTEGER,
            completed BOOLEAN,
            FOREIGN KEY (workoutID) REFERENCES Workout(workout_Id),
            FOREIGN KEY (activity_name) REFERENCES Activities(activity_name)
        )
    """)

    # 5. Sets Table (Composite Primary Key: liftID + set_number)
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS Sets (
            liftID INTEGER,
            set_number INTEGER,
            weight REAL,
            reps INTEGER,
            completed BOOLEAN,
            PRIMARY KEY (liftID, set_number),
            FOREIGN KEY (liftID) REFERENCES Lift(liftID)
        )
    """)

    # --- Add Test Data ---
    
    # Add a User
    cursor.execute("""
        INSERT OR REPLACE INTO User (username, password, name, skill_level, bench_max) 
        VALUES (?, ?, ?, ?, ?)
    """, ("gym_bro_99", "ironlifter1", "Chad Smith", "Advanced", 225))

    # Add an Activity
    cursor.execute("""
        INSERT OR REPLACE INTO Activities (activity_name, main_muscle_group, description) 
        VALUES (?, ?, ?)
    """, ("Bench Press", "Chest", "Lay on bench and push weight up."))

    conn.commit()
    print("Gym Database initialized with Relational Integrity.\n")

    # Verification Print
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table';")
    tables = cursor.fetchall()
    print("Created Tables:", [t[0] for t in tables])

    conn.close()

if __name__ == "__main__":
    init_db()