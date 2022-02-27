import socket
google = False
if google:
    host = "www.google.com"
    port = 80

    client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    client.connect((host,port))
    client.send(b"GET / HTTP/1.1\r\nHost: www.google.com\r\n\r\n")
else:
#     host = "whois.verisign-grs.com"
#     port = 43
#     payload = b"yahoo.com"
#     client = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
#     client.connect((host,port))
#     client.send(payload)
    HOST = "whois.verisign-grs.com"  # The server's hostname or IP address
    PORT = 43  # The port used by the server
    payload = b"yahoo.com\r\n"
    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.connect((HOST, PORT))
        s.sendall(payload)
        data = s.recv(1024)

    print(f"Received {data!r}")

# response = client.recv(4096)
# print(response)


