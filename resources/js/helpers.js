import { addHours, format } from 'date-fns'

export const isImage = (attachment) => {
  let mime = attachment.mime || attachment.type
  mime = mime.split('/')
  return mime[0].toLowerCase() === 'image'
}


// ajouter 2h à l'heure affichée pour être en heure locale
export const adjustTime = (date) => {
  const adjustHours =  addHours(new Date(date), 2)
  return format(adjustHours, 'y-MM-dd HH:mm:ss')
}
