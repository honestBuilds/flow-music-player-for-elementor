def sort_descending(input_list):
    """
    Sorts a list in descending order.

    Parameters:
    input_list (list): The list to be sorted.

    Returns:
    list: The sorted list in descending order.
    """
    return sorted(input_list, reverse=True)


# Example usage
if __name__ == "__main__":
    sample_list = [5, 2, 9, 1, 5, 6]
    sorted_list = sort_descending(sample_list)
    print("Sorted list in descending order:", sorted_list)
