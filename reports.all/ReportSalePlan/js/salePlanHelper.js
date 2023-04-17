import moment from "moment";

export const getTextMonth = (date) => {
    return moment(date, 'DD.MM.YYYY').locale('ru').format('MMMM');
}

export const getDataCounter = (data) => {
    const counter = {
        all: 0,
        included: 0,
        excluded: 0,
        unhandled: 0
    };

    if(!(data instanceof Array)) return counter

    counter.included = data.filter(item => item.dealStatus && !!item.dealStatus_date).length
    counter.excluded = data.filter(item => !item.dealStatus && !!item.dealStatus_date).length
    counter.all = data.length

    counter.unhandled = counter.all - counter.included - counter.excluded
    return counter
}

export const findReports = (reports,reportName, type, userId) => {
    let report = reports.find(i => i.name === reportName)
    let l = report[type].filter(i => i.userId === userId)

    if(type === 'one' ){
        return l[0] ? l[0].value : 0
    }else {
        return l.length
    }
}

export const findReport = (report,reportName, type, userId) => {
    let l = report[type].filter(i => i.userId === userId)
    if(type === 'one' ){
        return l[0] ? l[0].value : 0
    }else {
        return l.length
    }
}