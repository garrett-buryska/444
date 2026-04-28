import os
import sqlite3

# Path to your database file
DB_PATH = os.path.join(os.path.dirname(__file__), "gym_app.db")

def drop_weight_column():
    conn = sqlite3.connect(DB_PATH)
    cursor = conn.cursor()
    
    try:
        # This will fail if your SQLite version is older than 3.35.0
        cursor.execute("ALTER TABLE Sets DROP COLUMN weight;")
        conn.commit()
        print("Column 'weight' dropped successfully.")
    except sqlite3.OperationalError as e:
        print(f"Error: {e}")
        print("Note: If you get a 'syntax error', your SQLite version may be older than 3.35.0.")
    finally:
        conn.close()

if __name__ == "__main__":
    drop_weight_column()