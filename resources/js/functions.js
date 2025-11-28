import moment from 'moment';
import { toast } from '@/components/ui/toast';

export default {
    install(app) {
        app.config.globalProperties.$filters = {
            timestamp(value) {
                return moment(new Date(value)).format('DD MMM, YYYY h:mm a')
            },
            timestamp1(value, format) {
                return moment(new Date(value)).format(format)
            },
            date(value) {
                return moment(new Date(value)).format('MMM DD, YYYY')
            },
            time(value) {
                return moment(String(value)).format('h:mm a')
            },
            custom(value, format) {
                return moment(String(value)).format(format)
            },
            timeFT(value) {
                return moment(String(value), 'HH:mm:ss').format('h:mm a')
            },
            UnixTS(value) {
                return moment.unix(value / 1000).format('DD MMM, YY h:mm a')
            },
            formatMinutes(minutes) {
                const hours = Math.floor(minutes / 60);
                const mins = minutes % 60;
            
                const hoursStr = String(hours).padStart(2, '0');
                const minutesStr = String(mins).padStart(2, '0');
            
                return `${hoursStr} hrs : ${minutesStr} mins`;
            },
        }
        app.config.globalProperties.price_val = (currency, value) => {
            var formatter = new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: currency,
                maximumFractionDigits: 2,
            });
            return formatter.format(value);
        }
        app.config.globalProperties.m2Tz = (minutes) => {
            const hours = Math.floor(minutes / 60);
            const remainingMinutes = minutes % 60;
            const fractionalPart = remainingMinutes / 60;
            const timezoneOffset = hours + fractionalPart;
            return timezoneOffset;
        }
        app.directive('tooltip', function(el, binding){
            $(el).tooltip({
                title: binding.value,
                placement: binding.arg,
                trigger: 'hover'             
            })
        })
        app.directive('popover', function(el, binding){
            $(el).popover({
                title: binding.value[0],
                content: binding.value[1],
                html: true,
                placement: binding.arg,
                trigger: 'hover'             
            })
        })
        app.directive('mask-scroll', {
            mounted(el, binding) {
                const checkScroll = () => {
                  const scrollLeft = el.scrollLeft;
                  const scrollWidth = el.scrollWidth;
                  const clientWidth = el.clientWidth;
            
                  const canScroll = scrollWidth > clientWidth;
                  const isAtStart = scrollLeft <= 1;
                  const isAtEnd = Math.ceil(scrollLeft + clientWidth) >= scrollWidth;
          
                  // If cannot scroll at all, remove both masks
                  if (!canScroll) {
                    el.classList.remove('mask-left', 'mask-right');
                    return;
                  }
          
                  if (isAtStart) {
                      el.classList.add('mask-right');
                      el.classList.remove('mask-left', 'mask-both');
                    } else if (isAtEnd) {
                      el.classList.add('mask-left');
                      el.classList.remove('mask-right', 'mask-both');
                    } else {
                      el.classList.add('mask-both');
                      el.classList.remove('mask-left', 'mask-right');
                    }
                };
            
                // Add scroll listener
                el._maskScrollHandler = () => requestAnimationFrame(checkScroll);
                el.addEventListener('scroll', el._maskScrollHandler, { passive: true });
            
                // Add resize listener
                el._maskResizeHandler = () => requestAnimationFrame(checkScroll);
                window.addEventListener('resize', el._maskResizeHandler);
            
                // Initial check (in case of pre-scrolled state)
                requestAnimationFrame(checkScroll);
            },
            unmounted(el, binding) {
            el.removeEventListener('scroll', el._maskScrollHandler);
            window.removeEventListener('resize', el._maskResizeHandler);
            delete el._maskScrollHandler; 
            delete el._maskResizeHandler;
            }
        }); 
        app.directive('click-outside', {
            mounted(el, binding) {
              const handleClickOutside = (event) => {
                if (!el.contains(event.target) && el !== event.target) {
                    if (binding.value.closeCondition()) {
                        binding.value.closeAction();
                    }
                }
              };
              const handleSelfClick = (event) => { event.stopPropagation(); };
              el.addEventListener('click', handleSelfClick);
              document.addEventListener('click', handleClickOutside);
              el._clickOutsideHandler = handleClickOutside;
              el._selfClickHandler = handleSelfClick;
            },
            unmounted(el) {
              el.removeEventListener('click', el._selfClickHandler);
              document.removeEventListener('click', el._clickOutsideHandler);
              delete el._selfClickHandler;
              delete el._clickOutsideHandler;
            }
        });
        app.config.globalProperties.$debounce = (callback, delay) => {
            let timeoutId;
            return (...args) => {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    callback.apply(this, args);
                }, delay);
            };
        }
        app.config.globalProperties.validate = (evt) => {
            var key = evt.keyCode || evt.which;
            key = String.fromCharCode(key);
            var regex = /[0-9]|\./;
            if (!regex.test(key) && evt.keyCode != 13) {
                evt.returnValue = false;
                if (evt.preventDefault) evt.preventDefault();
            }
        },
        app.config.globalProperties.setCookie = (name, value, days) => {
            var expires = "";
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
        app.config.globalProperties.numberShort = (num, digits) => {
            const lookup = [
                { value: 1, symbol: "" },
                { value: 1e3, symbol: "k" },
                { value: 1e6, symbol: "M" },
                { value: 1e9, symbol: "G" },
                { value: 1e12, symbol: "T" },
                { value: 1e15, symbol: "P" },
                { value: 1e18, symbol: "E" }
            ];
            const rx = /\.0+$|(\.[0-9]*[1-9])0+$/;
            var item = lookup.slice().reverse().find(function(item) {
                return num >= item.value;
            });
            return item ? (num / item.value).toFixed(digits).replace(rx, "$1") + item.symbol : "0";
        }
        app.config.globalProperties.getCookie = (name) => {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
        app.config.globalProperties.encode = (val) => {
            return btoa(btoa(val))
        }
        app.config.globalProperties.decode_val = (val) => {
            return atob(atob(val))
        }
        app.config.globalProperties.isMobile = () => {
            let check = false;
            (function(a) {
                if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino|ipad|playbook|silk/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))
                check = true;
            })
            (navigator.userAgent || navigator.vendor || window.opera);
            return check;
        }        
    }
};