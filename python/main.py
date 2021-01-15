import requests
import urllib3
import xml.etree.ElementTree as ElementTree
import time
import mysql.connector
from mysql.connector import connect
from mysql.connector.pooling import MySQLConnectionPool
from multiprocessing.pool import ThreadPool


# credentials for telepresence units

username = "" #username used to log into all endpoints
password = "" #password used to log into all endpoints

db = {
    "database":"errorfinder",
    "port":"3306",
    "host":"",
    "user":"",
    "passwd":"",
}

num_pools = 0

def format_error_sql_input(text):
        if text is not None:
            text = text.replace("\"", "")
            text = text.replace("'", "")
            return text
        else: 
            return None

class DatabaseFunctions:

    def __init__(self, pool):
        global num_pools
        num_pools += 1
        self.con = pool.get_connection()
        self.cur = self.con.cursor()

    def log_endpoint(self, input, endpoint_id,):
        global job_id
        currenttime = time.strftime('%H:%M:%S')
        # ('{currenttime}','{input}',{endpoint_id},{job_id}
        query = "INSERT INTO errors_output (time, log, endpoint_id, job_id) VALUES (%s, %s, %s, %s)"
        self.cur.execute(query, (currenttime, input, endpoint_id, job_id))
        self.con.commit()

    def log_text(self, input):

        currenttime = time.strftime('%H:%M:%S')
        query = "INSERT INTO errors_output (time, log, job_id) VALUES (%s, %s, %s)"
        self.cur.execute(query, (currenttime, input, job_id))
        self.con.commit()


    def create_job(self, num_endpoints):

        date = time.strftime('%Y-%m-%d %H:%M:%S')
        query = f"INSERT INTO errors (date, current, total, active) VALUES ('{date}',0,{num_endpoints},1)"
        self.cur.execute(query)
        self.con.commit()
        job_id = self.cur.lastrowid

        return job_id

    def update_job(self, job_id, current):

        query = f"UPDATE errors SET current = {current} WHERE job_id = {job_id}"
        self.cur.execute(query)
        self.con.commit()

    def close_job(self, job_id):

        query = f"UPDATE errors SET active = 0, finished = 1 WHERE job_id = {job_id}"
        self.cur.execute(query)
        self.con.commit()
    
    def close(self):
        global num_pools
        num_pools -= 1
        self.con.close()

# /////////// main application ///////////




pool = MySQLConnectionPool(pool_name = "mypool", pool_size = 15, **db)

dbfunc = DatabaseFunctions(pool)


# check if there is already a job running
query = "SELECT active FROM errors ORDER BY job_id DESC LIMIT 1"
dbfunc.cur.execute(query)

last_job  = dbfunc.cur.fetchone()

if(last_job != None):
    last_job_active_status = last_job[0]
    if(last_job_active_status == 1):
        print('there is already a job running')
        raise SystemExit


query = "TRUNCATE errors_output"

dbfunc.cur.execute(query)



urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

dbfunc.cur.execute("SELECT * FROM endpoints")
endpoints = dbfunc.cur.fetchall()
num_endpoints = dbfunc.cur.rowcount

job_id = dbfunc.create_job(num_endpoints)

completed_queries = 0 

def query_endpoint(endpoint):

    global num_pools
    print(f"pools open: {num_pools}")

    print("connecting to " + str(endpoint))

    global completed_queries
    global job_id
    global pool

    global username
    global password

    dbfunc = DatabaseFunctions(pool)

    #check if php sent kill signal
    dbfunc.cur.execute("SELECT killed FROM errors ORDER BY job_id DESC LIMIT 1")
    killed = dbfunc.cur.fetchone()

    endpoint_id = endpoint[0]

    if(killed[0] == 1):
        dbfunc.log_endpoint('Scanner stopped manually', endpoint_id)
        dbfunc.close_job(job_id)
        dbfunc.close()
        raise SystemExit

    ip = endpoint[1]
    device_name = endpoint[2]
    device_type = endpoint[3]

    # i really dont know why either or will trigger it.... too tired to look into it more
    if(device_type is None or ''):
        dbfunc.log_endpoint(device_name + " has no device type!", endpoint_id)
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return
        
    if(ip is None or ''):
        dbfunc.log_endpoint(device_name + " has no IP!", endpoint_id)
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return
        

    s = requests.Session()

    try:
        dbfunc.log_endpoint('Connecting to ' + device_name, endpoint_id)
        configxml = s.get('https://' + ip + '/status.xml', auth=(username, password), verify=False, timeout=3)
    except requests.Timeout:
        dbfunc.log_endpoint('Timed out while connecting to ' + device_name, endpoint_id)
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return
    except Exception as e:
        dbfunc.log_text("Unknown error connecting to " + device_name + ", " + str(e));
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return
        
    try:
        tree = ElementTree.fromstring(str(configxml.text))
    except ElementTree.ParseError:
        dbfunc.log_endpoint("SyntaxError, 'error processing xml' on " + device_name + ", skipping...", endpoint_id)
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return

    if(tree.find('Diagnostics') is None):
        dbfunc.log_endpoint('No errors on ' + device_name, endpoint_id)
        completed_queries += 1
        dbfunc.update_job(job_id, completed_queries)
        dbfunc.close()
        return
        
    number_of_errors = 0
    for message in tree.find('Diagnostics').findall('Message'):
        description = level = ref = type = ''

        if (message.find('Description') is not None):
            description = message.find('Description').text
        if (message.find('Level') is not None):
            level = message.find('Level').text
        if (message.find('References') is not None):
            ref = message.find('References').text
        if (message.find('Type') is not None):
            type = message.find('Type').text

        date = time.strftime('%Y-%m-%d %H:%M:%S')
        
        description = format_error_sql_input(description)
        level = format_error_sql_input(level)
        ref = format_error_sql_input(ref)
        type = format_error_sql_input(type)
        
        query = f"insert into errors_errorlist (endpoint_id, job_id, date, level, reference, type, text) VALUES ('{endpoint_id}','{job_id}','{date}','{level}','{ref}','{type}','{description}')"
        dbfunc.cur.execute(query)
        dbfunc.con.commit()
        number_of_errors += 1
    
    completed_queries += 1
    print('updating at index ' + str(completed_queries))
    dbfunc.update_job(job_id,completed_queries)
    dbfunc.close()



def main():
    pool = ThreadPool(10)
    results = pool.map(query_endpoint, endpoints)
    pool.close()
    pool.join()
    dbfunc.log_text(f'Finished scanning {num_endpoints} endpoints!')
    dbfunc.close_job(job_id)
    dbfunc.close()

main()


