document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('form').addEventListener('submit', (ev) => {
        ev.preventDefault();

        let queryParts = [];

        // Service names:
        let str = document.getElementById('serviceNames').value.trim();
        if (str !== '') {
            str.split(',')
                .forEach((s) => {
                    queryParts.push('serviceNames[]=' + s.trim());
                });
        }

        str = document.getElementById('statusCode').value.trim();
        if (str !== '') {
            queryParts.push('statusCode=' + str);
        }

        str = document.getElementById('startDate').value.trim();
        if (str !== '') {
            queryParts.push('startDate=' + str);
        }

        str = document.getElementById('endDate').value.trim();
        if (str !== '') {
            queryParts.push('endDate=' + str);
        }

        const query = queryParts.join('&');
        const url = '/count?' + query;

        fetch(url, {
            method: 'GET',
        })
            .then((response) => {
                return response.json();
            })
            .then((json) => {
                document.getElementById('result').innerText = json.counter;
            })
            .catch(() => {
                document.getElementById('result').innerText = 'Error!';
            });
    });
});


