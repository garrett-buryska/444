import sqlite3
import os

def add_set_text_column():
    # Path to your database file
    db_path = 'gym_app.db'
    
    if not os.path.exists(db_path):
        print(f"Error: Could not find database at {db_path}")
        return

    try:
        conn = sqlite3.connect(db_path)
        cursor = conn.cursor()
        
        # Add the new column
        # TEXT is the safest data type for a string
        cursor.execute("ALTER TABLE Sets ADD COLUMN set_text TEXT")
        
        conn.commit()
        print("Success: 'set_text' column added to the 'Sets' table.")
        
    except sqlite3.OperationalError as e:
        # This catches errors, such as if the column already exists
        if "duplicate column name" in str(e).lower():
            print("The column 'set_text' already exists in the table.")
        else:
            print(f"An error occurred: {e}")
    finally:
        conn.close()

if __name__ == "__main__":
    add_set_text_column()