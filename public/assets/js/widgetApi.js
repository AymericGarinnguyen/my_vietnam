/**
 * fetch weather of the selected city
 * 
 * @param {string} city 
 */
function weather(city) {
  fetch(
    `https://api.weatherapi.com/v1/forecast.json?key=883462596fd54295bd3135305212207&q=${city}&days=4&aqi=no&alerts=no&lang=fr`
  )
    .then((response) => response.json())
    .then((data) => {
      // place current weather informations in div .current-weather
      document.querySelector(".current-weather").innerHTML = `
                <figure>         
                    <img src="${data["current"]["condition"]["icon"]}" alt="Weather icon" title="${data["current"]["condition"]["text"]}"></img>
                    <figcaption>
                        <p>Aujourd'hui</p>
                        <p>Température : ${data["current"]["temp_c"]}°C</p>
                    </figcaption>
                </figure> 
            `;
      // save the forecast weather in constante
      const forecastDays = data["forecast"]["forecastday"];
      // remove the first element because it is equal to the current weather
      forecastDays.shift();
      // place each forecast weather informations in div .forecast-weather
      for (const forecastDay of forecastDays) {
        const date = new Date(forecastDay["date"]);
        const day = ("0" + date.getDate()).slice(-2);
        const month = ("0" + (date.getMonth() + 1)).slice(-2);
        document.querySelector(".forecast-weather").innerHTML += `
                    <figure class="each-day">              
                        <img src="${forecastDay["day"]["condition"]["icon"]}" alt="Weather icon" title="${forecastDay["day"]["condition"]["text"]}"></img>
                        <figcaption>
                            <p>${day}/${month}</p>
                            <p class='temperature'>${forecastDay["day"]["avgtemp_c"]}°C</p>
                        </figcaption>
                    </figure>  
                `;
      }
    });
}

/**
 * Get actual local time
 */
function actualTime() {
  const time = document.querySelector("#actual-time");
  // catch local time
  const date = new Date(
    new Date().toLocaleString("en-US", { timeZone: "Asia/Saigon" })
  );
  const hours = ("0" + date.getHours()).slice(-2);
  const minutes = ("0" + date.getMinutes()).slice(-2);
  // place local time in const time
  time.innerHTML = `${hours}:${minutes}`;
  // refresh each minutes
  setInterval(actualTime, 1000 * 60);
}

/**
 * fetch the exchange rate
 * 
 * @param {object} rate 
 * @param {object} euro 
 * @param {object} dong 
 */
function currencyChange(rate, euro, dong) {
    fetch(`https://api.exchangerate.host/latest?base=EUR&symbols=VND`)
      .then((response) => response.json())
      .then((data) => {
        // save vientam dong rate
        const rateValue = data["rates"]["VND"];
        // write it in variable rate in french number format
        rate.innerHTML =
          "1 Euro vaut " +
          new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 10 }).format(
            rateValue
          ) +
          " Dong";
        // write rateValue in dong input in french number format
        dong.value = new Intl.NumberFormat("fr-FR", {
          maximumFractionDigits: 0,
        }).format(rateValue);
        // on euro input key up
        euro.addEventListener("keyup", function (e) {
          // stop refresh
          e.preventDefault();
          // if value is not a number => 0
          if (isNaN(this.value)) {
            this.value = 0;
          }
          // write conversion rate value in dong input
          dong.value = new Intl.NumberFormat("fr-FR", {
            maximumFractionDigits: 0,
          }).format(rateValue * this.value);
        });
        // on dong input key up
        dong.addEventListener("keyup", function (e) {
          // stop refresh
          e.preventDefault();
          // if value is not a number => 0
          if (isNaN(this.value)) {
            this.value = 0;
          }
          // write conversion rate value in euro input
          euro.value = new Intl.NumberFormat("fr-FR", {
            maximumFractionDigits: 2,
          }).format(this.value / rateValue);
        });
      });
}

export { weather, actualTime, currencyChange };
