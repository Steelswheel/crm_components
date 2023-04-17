// import {cloneDeep, isEqual} from 'lodash'

export const aliasFormat = (data, attributesAlias,isAddOldAttribute = false) => {


    let newObject = {}
    for (let item in data) {

        if ( Object.prototype.hasOwnProperty.call(attributesAlias,item)) {
            newObject[attributesAlias[item]] = data[item]
            if (isAddOldAttribute) newObject[attributesAlias[item]]['oldAttribute'] = item

        } else if (Object.values(attributesAlias).some(i => i === item)) {

            newObject[item] = data[item]
            if (isAddOldAttribute) newObject[item]['oldAttribute'] = item
        }

    }


    return newObject

}

// Установка title и типы для дефелтных полей
export const titleAndTypeHelper = (attributes) => {

    const keys = Object.keys(attributes);
    for (let item of keys) {

        switch (item) {
            case 'PHONE' : attributes[item].type = 'phone'; break;
            case 'EMAIL' : attributes[item].type = 'email'; break;
        }


        if (attributes[item].title.indexOf('UF_') === 0) {
            attributes[item].title = attributes[item].formLabel
        }
    }

    return attributes
}

// FIX position sticky
export const fixPositionSticky = (elHeader, boxShadow = 'rgb(0 0 0) 0px 9px 16px -16px', offset = 0) => {
    const eventScroll = () => {
        if (elHeader) {
            const elWrap = elHeader.parentNode;

            if (elWrap) {
                const cor = elWrap.getBoundingClientRect();

                if (cor.y < 0) {
                    elHeader.style.top = `${offset ? -cor.y + offset : -cor.y}px`;
                    elHeader.style.boxShadow = boxShadow;
                } else {
                    elHeader.style.top = `0px`;
                    elHeader.style.boxShadow = ``;
                }
            }
        }
    }

    window.removeEventListener('scroll', eventScroll);
    window.addEventListener('scroll', eventScroll);
}


