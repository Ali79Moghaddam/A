let _id = 100000;
function makeId() {
    return (++_id);
}

const state = {
    duties: [],
    days: {
        'شنبه': {
            records:[],
        },
        'یک شنبه': {
            records: [],
        },
        'دو شنبه': {
            records: [],
        },
        'سه شنبه': {
            records: [],
        },
        'چهار شنبه': {
            records: [],
        },
        'پنج شنبه': {
            records: [],
        },
        'جمعه': {
            records: [],
        },
    },
    description: "",
};

const elems = {
    dailyreportForm: document.getElementById('dailyreport-form'),
};

function addRecordAction(day) {
    state.days[day].records.push({
        id: makeId(),
        duty: "select duty",
        startTime: "0:0",
        studyingTime: 0,
        description: "",
        recent: 1,
    });
    render();
}   

function updateState(that, day, id, which) {
    if(which == "tot_description") {
        state.description = that.value;
        return;
    }
    state.days[day].records.forEach(function(record) {
        if(record.id == id) {
            switch(which) {
                case "duty" :
                    record.duty = that.value;
                    return;
                case "startTime" :
                    record.startTime = that.value;
                    return;
                case "studyingTime" :
                    record.studyingTime = that.value;
                    return;
                case "description" :
                    record.description = that.value;
                    return;
                case "delete" :
                    record.deleted = 1;
                    console.log(state);
                    render();
                    return;
            }  
        }
    });
}

function createDutySelect(record, day) {
    let selectedDuty = record.duty;
    let select;
    select = `<select class='select' onchange="updateState(this, '${day}', '${record.id}', 'duty')">`;
    state.duties.forEach(function(duty) {
        if(duty === selectedDuty)
            select += `<option selected="selected" value="${duty}">${duty}</option>`;
        else 
            select += `<option value="${duty}">${duty}</option>`;
    });
    select += `</select>&nbsp`;
    return select;
}

function createTimeSelect(record, day) {
    let startTime = record.startTime;
    let select;
    select = `<select class="select" onchange="updateState(this, '${day}', '${record.id}', 'startTime')">`;
    for(let hour = 0; hour < 24; hour++) {
        for(let min = 0; min < 60; min += 15) {
            if(startTime == (hour+":"+min)) 
                select += `<option selected="selected" value="${hour}:${min}">${min} : ${hour}</option>`;
            else 
                select += `<option value="${hour}:${min}">${min} : ${hour}</option>`;
        }
    }
    select += `</select>&nbsp`;
    return select;
}

function createStudyingTime(record, day) {
    let select = `<input type="number" class='text_box' value="${record.studyingTime}" onchange="updateState(this, '${day}', '${record.id}', 'studyingTime')" />&nbsp`;
    return select;
}

const createDescription = (record, day) => {
    let select = `<input type="text" class='text_box' class='description' value="${record.description}" onchange="updateState(this, '${day}', '${record.id}', 'description')" placeholder="Description" />&nbsp`;
    return select;
}

const createDeleteButton = (record, day) => {
    let select = `<button type="button" class='btn_report' onclick="updateState(this, '${day}', '${record.id}', 'delete')">پاک کردن</button>&nbsp`;
    return select;
}


const render = () => {
    elems.dailyreportForm.innerHTML = '';
    for(day in state.days) {
        const dayElem = document.createElement('div');
        dayElem.innerHTML = `<p>${day}</p>`;
        state.days[day].records.forEach(function(record) {
            if(record.deleted != 1) {
                const newRecord = document.createElement('div');
                newRecord.innerHTML += createDutySelect(record, day);
                newRecord.innerHTML += createTimeSelect(record, day);
                newRecord.innerHTML += createStudyingTime(record, day);
                newRecord.innerHTML += createDescription(record, day);
                newRecord.innerHTML += createDeleteButton(record, day);
                newRecord.innerHTML += "<br><br>";
                dayElem.appendChild(newRecord);
            }
        });
        dayElem.innerHTML += `<button type='button' class='btn_report' onclick="addRecordAction('${day}')" value='add'>اضافه کردن</button><br>`;
        elems.dailyreportForm.appendChild(dayElem);
    }
    const description = document.createElement('div');
    description.innerHTML += `<br><center><textarea cols='100' rows='5' onchange="updateState(this, 'N', 'N', 'tot_description')">${state.description}</textarea><center><br>`;
    elems.dailyreportForm.appendChild(description);
}

const sendData = () => {
    const httpc = new XMLHttpRequest(); // simplified for clarity
    const url = "json-receive.php";
    httpc.open("GET", url+"?state="+JSON.stringify(state), true); // sending as POST
    httpc.onreadystatechange = function() { //Call a function when the state changes.
        if(httpc.readyState == 4 && httpc.status == 200) { // complete and no errors
            alert(httpc.responseText); // some processing here, or whatever you want to do with the response
        }
    };
    httpc.send();
    for(day in state.days) {
        state.days[day].records.forEach(function(record) {
            record.recent = 0;
        });
    }
    console.log(state);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
}

const initialState = (courseId, weekNum, firstState, dutiesList, description) => {
    dutiesList = Object.values(dutiesList);
    dutiesList.forEach( (duty) => {
        state.duties.push(duty.duty);
    });
    preState = Object.values(firstState);
    weekDaysArray = Object.keys(state.days);
    weekDaysArray.forEach((day, index) => {
        for(let i = 0; i < preState.length; i++) {
            if(preState[i].weekday == index){
                state.days[day].records.push({
                    id: preState[i].id,
                    duty: state.duties[preState[i].duty-1],
                    startTime: preState[i].start,
                    studyingTime: preState[i].studyingtime,
                    description: preState[i].description,
                    timemodified: preState[i].timemodified,
                    recent: 0
                });
            }
        }
    });
    state.description = description;
    state.courseId = courseId;
    state.weekNum = weekNum;
    console.log(weekNum);
}