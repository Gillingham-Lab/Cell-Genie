export default class {
    #prefix

    constructor(prefix)
    {
        this.#prefix = prefix;
    }

    get(field)
    {
        let values = this.load();

        if (field in values) {
            return values[field];
        } else {
            return null;
        }
    }

    put(field, value)
    {
        let values = this.load();

        values[field] = value;

        this.save(values);
    }

    load()
    {
        let values = localStorage.getItem(this.#prefix);
        if (!values) {
            values = {};
        } else {
            values = JSON.parse(values);
        }

        return values;
    }

    save(newValues)
    {

        localStorage.setItem(this.#prefix, JSON.stringify(newValues));
    }
}