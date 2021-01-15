import mysql.connector
import subprocess
import requests
import datetime
from collections import Counter

# used in the Webex Bot message, the location where Error Finder is running on your webserver
site_root = "localhost/"

api_token = "" # your webex bot api token
room_id = "" # the room that the webex bot will post the message to
# i used the word space during my presentation, i meant room, please can I still win? It's just really confusing... Space, room, same thing???

db = mysql.connector.connect(
    database="errorfinder",
    port="",
    host="",
    user="",
    passwd="",
)

cur = db.cursor()

#if dailyreport can't run main.py, try changing py to python3, or the absolute path of the python runtime
subprocess.call("py main.py", shell=True)

query = "SELECT job_id FROM errors ORDER BY job_id DESC LIMIT 1"
cur.execute(query)
job = cur.fetchone()

thisjob = job[0] # the most recent job.
lastjob = thisjob - 1 #job from yesterday probably

print('this job is ' + str(thisjob))

query = f"SELECT * FROM errors_errorlist WHERE job_id = {thisjob}"
cur.execute(query)
errors = cur.fetchall() # endpoint job date level ref type text

query = f"SELECT * FROM errors_errorlist WHERE job_id = {lastjob}"
cur.execute(query)
last_job_errors = cur.fetchall() # errorid endpoint job date level ref type text


last_job_error_array = []

# gets the errors from the last job given an endpoint id
def get_errors_from_eid(endpoint_id):
    errors = []
    for error in last_job_errors:
        if error[1] is endpoint_id:
            errors.append(error)
    return errors


num_errors = 0

error_levels = []

for error in errors:
    endpoint_id = error[1]
    error_text = error[7]
    last_job_errors_from_this_eid = get_errors_from_eid(endpoint_id)

    last_job_error_text = []
    for error in last_job_errors_from_this_eid:
        last_job_error_text.append(error[7])

    if error_text not in last_job_error_text:
        query = f"INSERT INTO errors_newerrors (error_id, job_id) VALUES ({error[0]}, {thisjob})"
        num_errors = num_errors + 1
        error_levels.append(error[4])
        cur.execute(query)
        db.commit()

print(str(num_errors) + ' new errors added')

x = datetime.datetime.now()

report_message_body = f'## Daily Error Report for {x.strftime("%b %d")}\\n'

report_message_body += f'There are **{num_errors}** new errors\\n\\n'


error_levels_unique = list(set(error_levels))
for level in error_levels_unique: 
     report_message_body += f"**{error_levels.count(level)}** errors of level **{level}**\\n"


report_message_body += f"[To view them all, click here!](http://{site_root}/errorfinder/daily-report/?job={thisjob})"

    
print(report_message_body)

url = "https://webexapis.com/v1/messages"

headers = {
    'Content-Type': "application/json",
    'Authorization': f"Bearer {api_token}"
    }

if num_errors is 0:
    payload = f"{{\n\t\"roomId\": \"{room_id}\",\n\t\"markdown\": \"## Daily Error Report for {x.strftime('%b %d')} \\nThere are **NO** new errors\"\n\t\n}}"
else:
    payload = f"{{\n\t\"roomId\": \"{room_id}\",\n\t\"markdown\": \"{report_message_body}\"\n\t\n}}"




response = requests.request("POST", url, data=payload, headers=headers)

print(response.text)