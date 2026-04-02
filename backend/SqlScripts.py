import sqlite3

def add_new_user(username, password, name, weight, height, skill):
    try:
        # 1. Connect to your existing gym database
        conn = sqlite3.connect("./gym_app.db")
        cursor = conn.cursor()

        # 2. Prepare the SQL command
        # We only fill the fields we have; the rest (like bench_max) will stay NULL
        sql = """
            INSERT INTO User (username, password, name, weight, height, skill_level)
            VALUES (?, ?, ?, ?, ?, ?)
        """
        
        # 3. Create a tuple with the data
        user_data = (username, password, name, weight, height, skill)

        # 4. Execute and Commit
        cursor.execute(sql, user_data)
        conn.commit()
        
        print(f"Successfully added user: {username}")

    except sqlite3.IntegrityError:
        print(f"Error: The username '{username}' already exists.")
    except Exception as e:
        print(f"An error occurred: {e}")
    finally:
        conn.close()

# --- Example Usage ---
if __name__ == "__main__":
    # You can change these values to test
    add_new_user(
        username="gbutt",
        password="pass",
        name="Alex Rivera",
        weight=185.5,
        height=70.0,
        skill="Intermediate"
    )