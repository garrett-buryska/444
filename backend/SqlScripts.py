import os
import sqlite3

# Path to your database file
DB_PATH = os.path.join(os.path.dirname(__file__), "gym_app.db")

def create_max_table():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()

    try:
        # Enable foreign key support
        cursor.execute("PRAGMA foreign_keys = ON;")

        # Create the "Max" table
        # We use double quotes around "Max" because it is a reserved SQL keyword
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS "Max" (
                activity_name TEXT,
                username TEXT,
                max_value REAL,
                PRIMARY KEY (activity_name, username),
                FOREIGN KEY (activity_name) REFERENCES Activities(activity_name),
                FOREIGN KEY (username) REFERENCES User(username)
            )
        """)
        
        conn.commit()
        print("Table 'Max' created successfully with Foreign Key constraints.")
        
    except sqlite3.Error as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    create_max_table()